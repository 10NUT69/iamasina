<?php

namespace App\Console\Commands;

use App\Models\EmailNotificationLog;
use App\Models\User;
use App\Notifications\MissingServiceImagesReminder;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Throwable;

class SendMissingServiceImageReminders extends Command
{
    protected $signature = 'services:send-missing-image-reminders
        {--date= : Data publicarii verificate, in format YYYY-MM-DD. Implicit este ziua de ieri.}
        {--dry-run : Afiseaza ce s-ar trimite, fara emailuri si fara loguri.}';

    protected $description = 'Trimite email utilizatorilor care au publicat ieri anunturi fara imagini.';

    public function handle(): int
    {
        $periodStart = $this->resolvePeriodStart();
        $dryRun = (bool) $this->option('dry-run');

        if (! $periodStart) {
            return self::FAILURE;
        }

        $periodEnd = $periodStart->copy()->endOfDay();
        $periodDate = $periodStart->toDateString();

        $stats = [
            'eligible_users' => 0,
            'eligible_services' => 0,
            'sent' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        $this->targetUsersQuery($periodStart, $periodEnd, $periodDate)
            ->chunkById(200, function (Collection $users) use ($periodStart, $periodEnd, $periodDate, $dryRun, &$stats): void {
                foreach ($users as $user) {
                    $services = $user->services->values();

                    if ($services->isEmpty()) {
                        continue;
                    }

                    $stats['eligible_users']++;
                    $stats['eligible_services'] += $services->count();

                    if ($dryRun) {
                        $this->line(sprintf(
                            'DRY RUN: user #%d <%s> ar primi reminder pentru %d anunturi.',
                            $user->id,
                            $user->email,
                            $services->count(),
                        ));

                        continue;
                    }

                    $log = $this->reserveNotification($user, $services, $periodStart, $periodEnd, $periodDate);

                    if (! $log) {
                        $stats['skipped']++;

                        continue;
                    }

                    try {
                        $user->notify(new MissingServiceImagesReminder($services, $periodStart));

                        $log->forceFill([
                            'status' => EmailNotificationLog::STATUS_SENT,
                            'sent_at' => now(),
                            'failed_at' => null,
                            'error_message' => null,
                        ])->save();

                        $stats['sent']++;
                    } catch (Throwable $e) {
                        report($e);

                        $log->forceFill([
                            'status' => EmailNotificationLog::STATUS_FAILED,
                            'failed_at' => now(),
                            'error_message' => mb_substr($e->getMessage(), 0, 2000),
                        ])->save();

                        $stats['failed']++;
                    }
                }
            });

        $this->info(sprintf(
            'Missing image reminders%s for %s: %d users, %d services, %d sent, %d skipped, %d failed.',
            $dryRun ? ' DRY RUN' : '',
            $periodDate,
            $stats['eligible_users'],
            $stats['eligible_services'],
            $stats['sent'],
            $stats['skipped'],
            $stats['failed'],
        ));

        return $stats['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function targetUsersQuery(Carbon $periodStart, Carbon $periodEnd, string $periodDate): Builder
    {
        return User::query()
            ->select(['id', 'name', 'email'])
            ->where('is_active', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->whereDoesntHave('emailNotificationLogs', function (Builder $query) use ($periodDate): void {
                $query->where('notification_type', EmailNotificationLog::TYPE_MISSING_SERVICE_IMAGES)
                    ->where('period_date', $periodDate);
            })
            ->whereHas('services', function (Builder $query) use ($periodStart, $periodEnd): void {
                $this->applyMissingImagesServiceScope($query, $periodStart, $periodEnd);
            })
            ->with(['services' => function ($query) use ($periodStart, $periodEnd): void {
                $this->applyMissingImagesServiceScope($query, $periodStart, $periodEnd)
                    ->select([
                        'id',
                        'user_id',
                        'title',
                        'brand',
                        'model',
                        'brand_id',
                        'model_id',
                        'published_at',
                        'images',
                        'status',
                    ])
                    ->with([
                        'brandRel:id,name,slug',
                        'modelRel:id,name,slug',
                    ])
                    ->orderBy('published_at')
                    ->orderBy('id');
            }])
            ->orderBy('id');
    }

    private function applyMissingImagesServiceScope($query, Carbon $periodStart, Carbon $periodEnd)
    {
        return $query
            ->where('status', 'active')
            ->whereNotNull('published_at')
            ->whereBetween('published_at', [$periodStart, $periodEnd])
            ->where(function (Builder $query): void {
                $query->whereNull('images')
                    ->orWhere('images', '[]')
                    ->orWhereJsonLength('images', 0);
            });
    }

    private function reserveNotification(
        User $user,
        Collection $services,
        Carbon $periodStart,
        Carbon $periodEnd,
        string $periodDate,
    ): ?EmailNotificationLog {
        try {
            return EmailNotificationLog::create([
                'user_id' => $user->id,
                'notification_type' => EmailNotificationLog::TYPE_MISSING_SERVICE_IMAGES,
                'period_date' => $periodDate,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'service_ids' => $services->pluck('id')->values()->all(),
                'service_count' => $services->count(),
                'status' => EmailNotificationLog::STATUS_RESERVED,
                'reserved_at' => now(),
            ]);
        } catch (QueryException $e) {
            if ($this->isUniqueConstraintViolation($e)) {
                return null;
            }

            throw $e;
        }
    }

    private function resolvePeriodStart(): ?Carbon
    {
        $timezone = config('app.timezone', 'Europe/Bucharest');
        $date = $this->option('date');

        if (! $date) {
            return Carbon::now($timezone)->subDay()->startOfDay();
        }

        if (! is_string($date) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->error('Optiunea --date trebuie sa fie in format YYYY-MM-DD.');

            return null;
        }

        try {
            $periodStart = Carbon::createFromFormat('Y-m-d H:i:s', $date.' 00:00:00', $timezone);
        } catch (Throwable) {
            $this->error('Optiunea --date trebuie sa fie o data calendaristica valida.');

            return null;
        }

        if (! $periodStart || $periodStart->toDateString() !== $date) {
            $this->error('Optiunea --date trebuie sa fie o data calendaristica valida.');

            return null;
        }

        return $periodStart->startOfDay();
    }

    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        $sqlState = (string) $e->getCode();
        $message = $e->getMessage();

        return str_starts_with($sqlState, '23')
            || str_contains($message, 'Duplicate entry')
            || str_contains($message, 'UNIQUE constraint failed');
    }
}

<?php

namespace App\Services;

use App\Models\DealerFeedRun;
use App\Models\DealerFeedSource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DealerFeedCheck
{
    public function __construct(private DealerFeedDownload $download, private DealerFeedPreview $preview)
    {
    }

    public function run(DealerFeedSource $source): ?string
    {
        try {
            $content = $this->download->fetch($source->url);
            $result = $this->preview->inspect($content, $source->mapping ?? [], $source->profile);
            if (! $result['item_count']) {
                throw new DealerFeedException('Feedul nu conține anunțuri.');
            }
        } catch (DealerFeedException $exception) {
            $this->clearPreview($source);
            return $exception->getMessage();
        } catch (Throwable) {
            $this->clearPreview($source);
            return 'Feedul nu a putut fi verificat.';
        }

        $file = 'dealer-feeds/'.$source->id.'/'.bin2hex(random_bytes(16)).'.'.$this->preview->format($source->profile);
        try {
            if (! Storage::disk('local')->put($file, $content)) {
                throw new DealerFeedException('Fișierul feedului nu a putut fi salvat.');
            }
            DB::transaction(function () use ($source, $file, $result) {
                $saved = DealerFeedSource::query()->lockForUpdate()->find($source->id);
                if (! $saved || ! $saved->matchesFeedSettings($source) || $saved->hasWorkInProgress()) {
                    throw new DealerFeedException('Sursa s-a schimbat sau are deja o lucrare în curs.');
                }
                $previous = $saved->runs()->find($saved->last_check['run_id'] ?? 0);
                if ($previous && $previous->import_status !== 'queued') {
                    $this->discardFile($previous, $saved, deleteRun: $previous->import_status !== 'prepared');
                }
                $run = $saved->runs()->create([
                    'status' => 'verified',
                    'profile' => $saved->profile,
                    'snapshot_path' => $file,
                    'result' => [
                        'item_count' => $result['item_count'],
                        'dealer_name' => $saved->dealer->company_name ?: $saved->dealer->name,
                        'source_name' => $saved->name,
                    ],
                ]);
                $saved->forceFill([
                    'last_checked_at' => now(),
                    'last_check' => $this->sourceResult($run, $source, $result),
                ])->save();
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($file);
            return $exception instanceof DealerFeedException ? $exception->getMessage() : 'Feedul nu a putut fi verificat.';
        }

        return null;
    }

    public function discardFile(DealerFeedRun $run, DealerFeedSource $source, bool $deleteRun = false): void
    {
        $path = $run->snapshot_path;
        if ($deleteRun) {
            $run->delete();
        } elseif ($path) {
            $run->update(['snapshot_path' => null]);
        }
        if ((int) ($source->last_check['run_id'] ?? 0) === $run->id) {
            $source->forceFill(['last_check' => null])->save();
        }
        if ($path) {
            DB::afterCommit(fn () => Storage::disk('local')->delete($path));
        }
    }

    private function clearPreview(DealerFeedSource $before): void
    {
        DB::transaction(function () use ($before) {
            $source = DealerFeedSource::query()->lockForUpdate()->find($before->id);
            if (! $source || ! $source->matchesFeedSettings($before)) {
                return;
            }
            $run = $source->runs()->whereNull('import_status')->find($source->last_check['run_id'] ?? 0);
            if ($run) {
                $this->discardFile($run, $source, deleteRun: true);
            }
        });
    }

    public function cleanup(): void
    {
        $past = now()->subHours(12);
        DealerFeedRun::query()
            ->where(fn ($runs) => $runs->where('status', 'failed')->orWhere('import_status', 'failed')
                ->orWhere(fn ($old) => $old->whereNull('import_status')->where('updated_at', '<', $past))
                ->orWhere(fn ($applied) => $applied->where('import_status', 'prepared')->whereNotNull('snapshot_path')))
            ->select(['id', 'source_id'])->chunkById(100, function ($runs) {
                foreach ($runs as $candidate) {
                    DB::transaction(function () use ($candidate) {
                        $source = DealerFeedSource::query()->lockForUpdate()->find($candidate->source_id);
                        $run = $source?->runs()->find($candidate->id);
                        if ($run && $run->import_status !== 'queued' && ($run->status === 'failed'
                            || in_array($run->import_status, ['prepared', 'failed'], true) || $run->updated_at->lt(now()->subHours(12)))) {
                            $this->discardFile($run, $source, deleteRun: $run->import_status !== 'prepared');
                        }
                    });
                }
            });
    }

    public function refreshMapping(DealerFeedSource $source): void
    {
        $run = $this->verifiedRun($source);
        try {
            $result = $this->preview->inspect($this->read($run), $source->mapping ?? [], $source->profile);
        } catch (DealerFeedException $exception) {
            $result = ['error' => $exception->getMessage()];
        }

        $source->last_check = $this->sourceResult($run, $source, $result);
    }

    public function verifiedRun(DealerFeedSource $source): DealerFeedRun
    {
        $run = $source->runs()->find($source->last_check['run_id'] ?? 0);
        if (! $run || $run->status !== 'verified' || $run->profile !== $source->profile || ! $run->snapshot_path) {
            throw new DealerFeedException('Testează feedul pentru a salva un fișier verificat înainte de probă.');
        }

        return $run;
    }

    public function read(DealerFeedRun $run): string
    {
        $content = Storage::disk('local')->get($run->snapshot_path);
        if ($content === null) {
            throw new DealerFeedException('Fișierul verificat lipsește. Testează din nou feedul.');
        }

        return $content;
    }

    private function sourceResult(DealerFeedRun $run, DealerFeedSource $source, array $result): array
    {
        return $result + ['run_id' => $run->id, 'tested_mapping' => $source->mapping ?? []];
    }
}

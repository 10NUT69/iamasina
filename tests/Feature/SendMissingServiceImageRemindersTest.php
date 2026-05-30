<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\County;
use App\Models\EmailNotificationLog;
use App\Models\Service;
use App\Models\User;
use App\Notifications\MissingServiceImagesReminder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SendMissingServiceImageRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_one_reminder_per_user_for_yesterdays_services_without_images(): void
    {
        Notification::fake();

        $this->ensureLegacyForeignKeyTablesExist();

        $user = User::factory()->create();
        $category = Category::create(['name' => 'Autoturisme', 'slug' => 'autoturisme']);
        $county = County::create(['name' => 'Bucuresti', 'slug' => 'bucuresti']);

        $firstService = $this->createService($user, $category, $county, [
            'title' => 'BMW 320d fara poze',
            'published_at' => Carbon::parse('2026-05-29 10:00:00'),
            'images' => [],
        ]);
        $secondService = $this->createService($user, $category, $county, [
            'title' => 'Audi A4 fara poze',
            'published_at' => Carbon::parse('2026-05-29 18:30:00'),
            'images' => [],
        ]);

        $this->createService($user, $category, $county, [
            'title' => 'Anunt cu poza',
            'published_at' => Carbon::parse('2026-05-29 12:00:00'),
            'images' => ['anunt-cu-poza.webp'],
        ]);
        $this->createService(null, $category, $county, [
            'title' => 'Guest fara poze',
            'published_at' => Carbon::parse('2026-05-29 13:00:00'),
            'images' => [],
        ]);
        $this->createService($user, $category, $county, [
            'title' => 'Anunt de azi',
            'published_at' => Carbon::parse('2026-05-30 09:00:00'),
            'images' => [],
        ]);

        $this->artisan('services:send-missing-image-reminders', ['--date' => '2026-05-29'])
            ->assertExitCode(0);

        Notification::assertSentTo(
            $user,
            MissingServiceImagesReminder::class,
            function (MissingServiceImagesReminder $notification) use ($firstService, $secondService, $user): bool {
                $mail = $notification->toMail($user);

                return $notification->serviceIds() === [$firstService->id, $secondService->id]
                    && $mail->viewData['serviceCount'] === 2
                    && $mail->viewData['periodLabel'] === '29.05.2026';
            }
        );

        $log = EmailNotificationLog::first();

        $this->assertNotNull($log);
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame(EmailNotificationLog::TYPE_MISSING_SERVICE_IMAGES, $log->notification_type);
        $this->assertSame(EmailNotificationLog::STATUS_SENT, $log->status);
        $this->assertSame([$firstService->id, $secondService->id], $log->service_ids);
        $this->assertSame(2, $log->service_count);

        Notification::fake();

        $this->artisan('services:send-missing-image-reminders', ['--date' => '2026-05-29'])
            ->assertExitCode(0);

        Notification::assertNothingSent();
        $this->assertSame(1, EmailNotificationLog::count());
    }

    private function createService(?User $user, Category $category, County $county, array $overrides = []): Service
    {
        return Service::create(array_merge([
            'user_id' => $user?->id,
            'category_id' => $category->id,
            'county_id' => $county->id,
            'title' => 'Anunt test',
            'description' => 'Descriere anunt test',
            'city' => 'Bucuresti',
            'phone' => '0700000000',
            'email' => $user?->email,
            'images' => [],
            'status' => 'active',
            'published_at' => Carbon::parse('2026-05-29 10:00:00'),
        ], $overrides));
    }

    private function ensureLegacyForeignKeyTablesExist(): void
    {
        if (! Schema::hasTable('localities')) {
            Schema::create('localities', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('county_id')->nullable();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->timestamps();
            });
        }
    }
}

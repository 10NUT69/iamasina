<?php

namespace Tests\Feature;

use App\Jobs\ProcessServiceImages;
use App\Models\Category;
use App\Models\County;
use App\Models\Service;
use App\Support\ServiceImageStorage;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProcessServiceImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_processing_uses_an_immutable_primary_filename_and_is_idempotent_on_retry(): void
    {
        $originalStoragePath = $this->app->storagePath();
        $temporaryStoragePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'iaauto-service-images-'.Str::uuid();
        $originalLocalRoot = config('filesystems.disks.local.root');
        $originalPublicRoot = config('filesystems.disks.public.root');

        File::ensureDirectoryExists($temporaryStoragePath.DIRECTORY_SEPARATOR.'app/private');
        File::ensureDirectoryExists($temporaryStoragePath.DIRECTORY_SEPARATOR.'app/public');

        $this->app->useStoragePath($temporaryStoragePath);
        config()->set('filesystems.disks.local.root', storage_path('app/private'));
        config()->set('filesystems.disks.public.root', storage_path('app/public'));
        Storage::forgetDisk('local');
        Storage::forgetDisk('public');

        try {
            if (! Schema::hasTable('localities')) {
                Schema::create('localities', function (Blueprint $table): void {
                    $table->id();
                    $table->foreignId('county_id')->nullable();
                    $table->string('name')->nullable();
                    $table->string('slug')->nullable();
                    $table->timestamps();
                });
            }

            if (! Schema::hasTable('brands')) {
                Schema::create('brands', function (Blueprint $table): void {
                    $table->id();
                });
            }

            $category = Category::create([
                'name' => 'Autoturisme',
                'slug' => 'autoturisme',
            ]);
            $county = County::create([
                'name' => 'Buzău',
                'slug' => 'buzau',
            ]);
            $service = Service::create([
                'category_id' => $category->id,
                'county_id' => $county->id,
                'title' => 'Anunț test imagini',
                'description' => 'Descriere test pentru procesarea imaginilor.',
                'city' => 'Buzău',
                'images' => ['legacy-service-image.webp'],
                'status' => 'pending',
            ]);

            $pendingPath = sprintf(
                'service-image-queue/%d/550e8400-e29b-41d4-a716-446655440000.jpg',
                $service->id
            );
            $source = UploadedFile::fake()->image('replacement.jpg', 1200, 900);
            Storage::put($pendingPath, File::get($source->getRealPath()));

            $extension = function_exists('imagewebp') ? 'webp' : 'jpg';
            $expectedFilename = ServiceImageStorage::processedImageFilename(
                'auto-model-de-vanzare-buzau-buzau',
                $service->id,
                $pendingPath,
                $extension
            );

            (new ProcessServiceImages($service->id, [$pendingPath], false, 0))->handle();

            $this->assertSame(
                [$expectedFilename, 'legacy-service-image.webp'],
                $service->refresh()->images
            );
            $this->assertFileExists(storage_path('app/public/services/'.$expectedFilename));
            $this->assertFileExists(storage_path(
                'app/public/'.ServiceImageStorage::CARD_THUMBNAIL_DIR.'/'.$expectedFilename
            ));
            Storage::assertMissing($pendingPath);

            Storage::put($pendingPath, File::get($source->getRealPath()));
            (new ProcessServiceImages($service->id, [$pendingPath], false, 0))->handle();

            $this->assertSame(
                [$expectedFilename, 'legacy-service-image.webp'],
                $service->refresh()->images
            );
            Storage::assertMissing($pendingPath);
        } finally {
            $this->app->useStoragePath($originalStoragePath);
            config()->set('filesystems.disks.local.root', $originalLocalRoot);
            config()->set('filesystems.disks.public.root', $originalPublicRoot);
            Storage::forgetDisk('local');
            Storage::forgetDisk('public');
            File::deleteDirectory($temporaryStoragePath);
        }
    }
}

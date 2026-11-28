<?php

namespace App\Jobs;

use App\Models\DealerFeedListing;
use App\Models\DealerFeedTrial;
use App\Models\Service;
use App\Support\ServiceImageStorage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Throwable;

class ProcessServiceImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const LARGE_IMAGE_QUALITY = 84;
    private const CARD_THUMBNAIL_WIDTH = 720;
    private const CARD_THUMBNAIL_HEIGHT = 540;
    private const CARD_THUMBNAIL_QUALITY = 80;
    private const WATERMARK_OPACITY = 82;
    private const WATERMARK_WIDTH_RATIO = 0.22;
    private const WATERMARK_OFFSET_RATIO = 0.025;

    public int $tries = 3;

    public int $timeout = 300;

    public ?array $feedTrialContext = null;

    public ?array $feedImportContext = null;

    public bool $failOnTimeout = false;

    private bool $watermarkWarningLogged = false;

    public function __construct(
        public int $serviceId,
        public array $pendingImages,
        public bool $replaceExisting = false,
        public ?int $primaryPendingIndex = null
    ) {
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping(self::class . ':service-images-global'))
                ->shared()
                ->releaseAfter(15)
                ->expireAfter($this->timeout + 120),
        ];
    }

    public function handle(): void
    {
        $context = $this->feedTrialContext;
        $trial = $context ? DealerFeedTrial::currentRun($context['id'], $context['run']) : null;
        if ($context && $trial?->listing->service_id !== $this->serviceId) {
            Storage::delete($this->pendingImages);
            return;
        }
        $importContext = $this->feedImportContext;
        $updatesGallery = $importContext['gallery'] ?? false;
        $importItem = $importContext
            ? DealerFeedListing::importItem($importContext['id'], $importContext['run'], 'processing', $updatesGallery)->first()
            : null;
        if ($importContext && $importItem?->service_id !== $this->serviceId) {
            Storage::delete($this->pendingImages);
            return;
        }

        $service = Service::query()
            ->with(['brandRel', 'modelRel', 'county', 'locality', 'generation.model.brand'])
            ->find($this->serviceId);

        if (!$service || (($trial || $importItem) && $service->status !== ($updatesGallery ? 'active' : 'pending'))) {
            $trial?->finish(0, [...($trial->messages ?? []), 'Anunțul de probă nu mai este în așteptare.'], 'failed');
            $importItem?->finishImport('failed', 'Starea anunțului nu mai permite actualizarea pozelor.', $updatesGallery);
            Storage::delete($this->pendingImages);
            return;
        }

        $trialMessages = $trial?->messages ?? [];
        $previousImages = ($trial || $importItem) ? $this->normalizeImages($service->images) : [];
        $existingImages = $this->replaceExisting ? [] : $this->normalizeImages($service->images);
        $pendingImages = array_values($this->pendingImages);
        $availableSlots = max(0, ServiceImageStorage::MAX_IMAGES - count($existingImages));
        $processableImages = array_slice($pendingImages, 0, $availableSlots);
        $skippedImages = array_slice($pendingImages, $availableSlots);

        foreach ($skippedImages as $pendingPath) {
            Storage::delete($pendingPath);
        }

        $processedImages = $this->renderImages($service, $processableImages, $trialMessages, $context['positions'] ?? []);

        if ($trial && $processedImages === []) {
            $trial->finish(0, $trialMessages, 'failed');
            Storage::delete($processableImages);
            return;
        }

        $images = array_values(array_slice(
            array_unique(array_merge($existingImages, $processedImages), SORT_REGULAR),
            0,
            ServiceImageStorage::MAX_IMAGES
        ));

        $primaryPendingIndex = $this->primaryPendingIndex();

        if ($primaryPendingIndex !== null && isset($processedImages[$primaryPendingIndex])) {
            $primaryImage = $processedImages[$primaryPendingIndex];
            $images = array_values(array_unique(array_merge([$primaryImage], $images)));
        }

        $images = array_slice($images, 0, ServiceImageStorage::MAX_IMAGES);
        if ($importItem) {
            $saved = $this->finishFeedImport($importItem, $importContext['run'], $images, count($processedImages) === count($pendingImages), $updatesGallery);
            $displacedImages = $saved
                ? array_filter($previousImages, fn ($image) => ! in_array(ServiceImageStorage::localServiceFilename($image), $images, true))
                : $processedImages;
            ServiceImageStorage::deleteServiceImages($displacedImages);
        } elseif ($trial) {
            $saved = DB::transaction(function () use ($trial, $service, $images, $processedImages, $trialMessages) {
                $current = Service::query()->lockForUpdate()->find($service->id);
                if (! $current || $current->status !== 'pending') {
                    $trial->finish(0, [...$trialMessages, 'Anunțul de probă nu mai este în așteptare.'], 'failed');
                    return false;
                }
                $current->images = $images;
                $current->save();
                $trial->finish(count($processedImages), $trialMessages, 'complete');
                return true;
            });
            ServiceImageStorage::deleteServiceImages($saved ? array_diff($previousImages, $images) : $processedImages);
        } else {
            $service->images = $images;
            if ($service->status === 'active') {
                $service->published_at = $service->published_at ?: now();
            }
            $service->save();
        }
        Storage::delete($processableImages);
    }

    protected function renderImages(Service $service, array $pendingImages, array &$trialMessages = [], array $positions = []): array
    {
        $processedImages = [];
        $manager = new ImageManager(new Driver());
        $extension = $this->targetExtension();
        $baseName = $this->baseImageName($service);

        foreach ($pendingImages as $index => $pendingPath) {
            $sourcePath = Storage::path($pendingPath);

            if (!is_file($sourcePath)) {
                if ($positions !== []) {
                    $trialMessages[] = 'Poza '.$positions[$index].': fișierul temporar lipsește.';
                }
                continue;
            }

            $targetName = ServiceImageStorage::processedImageFilename(
                $baseName,
                $service->id,
                $pendingPath,
                $extension
            );
            $targetPath = storage_path('app/public/services/' . $targetName);
            $thumbnailPath = storage_path('app/public/' . ServiceImageStorage::CARD_THUMBNAIL_DIR . '/' . $targetName);

            if (!is_dir(dirname($targetPath))) {
                mkdir(dirname($targetPath), 0755, true);
            }

            if (!is_dir(dirname($thumbnailPath))) {
                mkdir(dirname($thumbnailPath), 0755, true);
            }

            try {
                $image = $manager->read($sourcePath)->scaleDown(1600);
                $this->applyWatermark($image, $manager);
                $this->saveImage($image, $targetPath, $extension, self::LARGE_IMAGE_QUALITY);

                $this->createCardThumbnail($manager, $sourcePath, $thumbnailPath, $extension, $service->id, $pendingPath);

                $processedImages[] = $targetName;
            } catch (Throwable $exception) {
                if ($positions !== []) {
                    $trialMessages[] = 'Poza '.$positions[$index].': nu a putut fi procesată.';
                }
                Log::warning('Service image processing failed.', [
                    'service_id' => $service->id,
                    'source' => $pendingPath,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return $processedImages;
    }

    public function failed(?Throwable $exception): void
    {
        if ($this->feedImportContext) {
            $context = $this->feedImportContext;
            $updatesGallery = $context['gallery'] ?? false;
            $item = DealerFeedListing::importItem($context['id'], $context['run'], 'processing', $updatesGallery)->first();
            $item?->finishImport('failed', 'Workerul nu a putut termina procesarea pozelor.', $updatesGallery);
            Storage::delete($this->pendingImages);
        }
        if ($this->feedTrialContext) {
            $trial = DealerFeedTrial::currentRun($this->feedTrialContext['id'], $this->feedTrialContext['run']);
            $trial?->finish(0, [...($trial->messages ?? []), 'Jobul de procesare nu a putut termina proba.'], 'failed');
            Storage::delete($this->pendingImages);
        }
    }

    private function finishFeedImport(DealerFeedListing $item, int $runId, array $images, bool $complete, bool $updatesGallery): bool
    {
        return DB::transaction(function () use ($item, $runId, $images, $complete, $updatesGallery) {
            $service = Service::query()->lockForUpdate()->find($this->serviceId);
            $current = DealerFeedListing::importItem($item->id, $runId, 'processing', $updatesGallery)->lockForUpdate()->first();
            if (! $current) {
                return false;
            }
            $dealer = $current->source?->dealer;
            if (! $service || ($updatesGallery ? $service->status !== 'active' || ! $service->is_active
                : $service->status !== 'pending' || $service->published_at !== null)
                || $dealer?->id !== $service->user_id || $dealer->user_type !== 'dealer' || $dealer->is_active === false) {
                $current->finishImport('failed', 'Anunțul sau contul dealerului nu mai permite procesarea pozelor.', $updatesGallery);
                return false;
            }
            if (! $complete) {
                $current->finishImport('failed', 'Cel puțin o fotografie nu a putut fi procesată.', $updatesGallery);
                return false;
            }
            if ($updatesGallery && $current->sync_gallery['expected_images'] !== DealerFeedListing::photoFingerprint($service->images ?? [])) {
                $current->finishImport('failed', 'Galeria a fost modificată în timpul sincronizării.', updatesGallery: true);
                return false;
            }

            $service->images = $images;
            if ($updatesGallery) {
                $service->save();
                $current->finishImport('published', updatesGallery: true);
            } else {
                $current->publishImport($service);
            }
            return true;
        });
    }

    private function normalizeImages(mixed $images): array
    {
        if (is_string($images)) {
            $images = json_decode($images, true) ?: [];
        }

        return is_array($images) ? array_values(array_filter($images)) : [];
    }

    private function targetExtension(): string
    {
        return function_exists('imagewebp') ? 'webp' : 'jpg';
    }

    private function createCardThumbnail(
        ImageManager $manager,
        string $sourcePath,
        string $thumbnailPath,
        string $extension,
        int $serviceId,
        string $pendingPath
    ): void {
        try {
            $thumbnail = $manager
                ->read($sourcePath)
                ->cover(self::CARD_THUMBNAIL_WIDTH, self::CARD_THUMBNAIL_HEIGHT);

            $this->applyWatermark($thumbnail, $manager);
            $this->saveImage($thumbnail, $thumbnailPath, $extension, self::CARD_THUMBNAIL_QUALITY);
        } catch (Throwable $exception) {
            Log::warning('Service card thumbnail processing failed.', [
                'service_id' => $serviceId,
                'source' => $pendingPath,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function saveImage(ImageInterface $image, string $path, string $extension, int $quality): void
    {
        if ($extension === 'webp') {
            $image->toWebp($quality)->save($path);
            return;
        }

        $image->toJpeg($quality)->save($path);
    }

    private function applyWatermark(ImageInterface $image, ImageManager $manager): void
    {
        $logoPath = $this->watermarkLogoPath();

        if (!$logoPath) {
            $this->logWatermarkWarning('Watermark logo file was not found.');
            return;
        }

        try {
            $watermark = $manager->read($logoPath);
            $watermarkWidth = max(1, (int) round($image->width() * self::WATERMARK_WIDTH_RATIO));
            $offset = max(8, (int) round($image->width() * self::WATERMARK_OFFSET_RATIO));

            $watermark->scale($watermarkWidth);
            $image->place($watermark, 'bottom-right', $offset, $offset, self::WATERMARK_OPACITY);
        } catch (Throwable $exception) {
            $this->logWatermarkWarning('Watermark logo could not be read or applied.', [
                'logo' => $logoPath,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function watermarkLogoPath(): ?string
    {
        foreach ([
            public_path('images/iaauto-logo-watermark.png'),
            public_path('images/iaauto-logo-watermark.webp'),
            public_path('images/iaauto-logo-nav.png'),
            public_path('images/iaauto-logo-nav.webp'),
            public_path('images/iaauto-logo.png'),
            public_path('images/iaauto-logo.webp'),
            public_path('images/iaauto-logo-nav.svg'),
            public_path('images/iaauto-logo.svg'),
        ] as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function logWatermarkWarning(string $message, array $context = []): void
    {
        if ($this->watermarkWarningLogged) {
            return;
        }

        $this->watermarkWarningLogged = true;
        Log::warning($message, $context + ['service_id' => $this->serviceId]);
    }

    private function primaryPendingIndex(): ?int
    {
        return isset($this->primaryPendingIndex) ? $this->primaryPendingIndex : null;
    }

    private function baseImageName(Service $service): string
    {
        $brand = $service->brandRel?->slug
            ?: $service->brandRel?->name
            ?: $service->generation?->model?->brand?->slug
            ?: $service->generation?->model?->brand?->name
            ?: $service->brand
            ?: 'auto';

        $model = $service->modelRel?->slug
            ?: $service->modelRel?->name
            ?: $service->generation?->model?->slug
            ?: $service->generation?->model?->name
            ?: $service->model
            ?: 'model';

        $county = $service->county?->slug
            ?: $service->county?->name
            ?: 'romania';

        $city = $service->locality?->slug
            ?: $service->locality?->name
            ?: $service->city
            ?: $county;

        return implode('-', [
            $this->slugSegment($brand, 'auto'),
            $this->slugSegment($model, 'model'),
            'de-vanzare',
            $this->slugSegment($county, 'romania'),
            $this->slugSegment($city, 'oras'),
        ]);
    }

    private function slugSegment(?string $value, string $fallback): string
    {
        return Str::slug((string) $value) ?: $fallback;
    }
}

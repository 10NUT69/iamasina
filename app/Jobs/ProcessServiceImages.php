<?php

namespace App\Jobs;

use App\Models\Service;
use App\Support\ServiceImageStorage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
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
            (new WithoutOverlapping('service-images-global'))
                ->releaseAfter(15)
                ->expireAfter($this->timeout + 120),
        ];
    }

    public function handle(): void
    {
        $service = Service::query()
            ->with(['brandRel', 'modelRel', 'county', 'locality', 'generation.model.brand'])
            ->find($this->serviceId);

        if (!$service) {
            Storage::delete($this->pendingImages);
            return;
        }

        $existingImages = $this->replaceExisting ? [] : $this->normalizeImages($service->images);
        $processedImages = [];
        $manager = new ImageManager(new Driver());
        $extension = $this->targetExtension();
        $baseName = $this->baseImageName($service);
        $nextNumber = count($existingImages) + 1;

        $pendingImages = array_values($this->pendingImages);
        $availableSlots = max(0, 10 - count($existingImages));
        $processableImages = array_slice($pendingImages, 0, $availableSlots);
        $skippedImages = array_slice($pendingImages, $availableSlots);

        foreach ($skippedImages as $pendingPath) {
            Storage::delete($pendingPath);
        }

        foreach ($processableImages as $pendingPath) {
            $sourcePath = Storage::path($pendingPath);

            if (!is_file($sourcePath)) {
                continue;
            }

            $targetName = $this->availableImageName($baseName, $service->id, $nextNumber, $extension);
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
                $nextNumber++;
            } catch (Throwable $exception) {
                Log::warning('Service image processing failed.', [
                    'service_id' => $service->id,
                    'source' => $pendingPath,
                    'message' => $exception->getMessage(),
                ]);
            } finally {
                Storage::delete($pendingPath);
            }
        }

        $images = array_values(array_slice(array_merge($existingImages, $processedImages), 0, 10));

        $primaryPendingIndex = $this->primaryPendingIndex();

        if ($primaryPendingIndex !== null && isset($processedImages[$primaryPendingIndex])) {
            $primaryImage = $processedImages[$primaryPendingIndex];
            $images = array_values(array_unique(array_merge([$primaryImage], $images)));
        }

        $service->images = array_values(array_slice($images, 0, 10));
        if ($service->status === 'active') {
            $service->published_at = $service->published_at ?: now();
        }
        $service->save();
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

    private function availableImageName(string $baseName, int $serviceId, int &$number, string $extension): string
    {
        do {
            $name = "{$baseName}-{$serviceId}-{$number}.{$extension}";
            $number++;
        } while (is_file(storage_path('app/public/services/' . $name)));

        $number--;

        return $name;
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

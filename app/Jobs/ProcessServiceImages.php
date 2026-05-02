<?php

namespace App\Jobs;

use App\Models\Service;
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
use Throwable;

class ProcessServiceImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public int $serviceId,
        public array $pendingImages,
        public bool $replaceExisting = false
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
            return;
        }

        $existingImages = $this->replaceExisting ? [] : $this->normalizeImages($service->images);
        $processedImages = [];
        $manager = new ImageManager(new Driver());
        $extension = $this->targetExtension();
        $baseName = $this->baseImageName($service);
        $nextNumber = count($existingImages) + 1;

        foreach (array_slice($this->pendingImages, 0, max(0, 10 - count($existingImages))) as $pendingPath) {
            $sourcePath = Storage::path($pendingPath);

            if (!is_file($sourcePath)) {
                continue;
            }

            $targetName = $this->availableImageName($baseName, $service->id, $nextNumber, $extension);
            $targetPath = storage_path('app/public/services/' . $targetName);

            if (!is_dir(dirname($targetPath))) {
                mkdir(dirname($targetPath), 0755, true);
            }

            try {
                $image = $manager->read($sourcePath)->scaleDown(1600);

                if ($extension === 'webp') {
                    $image->toWebp(84)->save($targetPath);
                } else {
                    $image->toJpeg(84)->save($targetPath);
                }

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

        $service->images = array_values(array_slice(array_merge($existingImages, $processedImages), 0, 10));
        $service->status = 'active';
        $service->published_at = $service->published_at ?: now();
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

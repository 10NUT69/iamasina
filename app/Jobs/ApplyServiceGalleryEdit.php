<?php

namespace App\Jobs;

use App\Models\Service;
use App\Support\ServiceImageStorage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ApplyServiceGalleryEdit extends ProcessServiceImages
{
    public int $tries = 30;

    public bool $failOnTimeout = true;

    public function __construct(
        int $serviceId,
        array $pendingImages,
        public int $ownerId,
        public string $revision,
        public string $expectedStatus,
        public bool $expectedTrashed,
        public array $order,
    ) {
        parent::__construct($serviceId, $pendingImages);
    }

    public function handle(): void
    {
        $service = Service::withTrashed()
            ->with(['brandRel', 'modelRel', 'county', 'locality', 'generation.model.brand'])
            ->find($this->serviceId);
        if (!$this->matches($service)) {
            $this->keepPublishedGallery('datele galeriei s-au schimbat');
            return;
        }

        $processed = $this->renderImages($service, $this->pendingImages);
        if (count($processed) !== count($this->pendingImages)) {
            ServiceImageStorage::deleteServiceImages($processed);
            $this->keepPublishedGallery('cel puțin o poză nu a putut fi procesată');
            return;
        }

        $saved = DB::transaction(function () use ($processed) {
            $current = Service::withTrashed()->lockForUpdate()->find($this->serviceId);
            if (!$this->matches($current)) {
                return null;
            }

            $previous = ServiceImageStorage::listingImages($current->images);
            $gallery = array_map(fn ($item) => isset($item['existing'])
                ? $previous[$item['existing']]
                : $processed[$item['new']], $this->order);
            $current->images = $gallery;
            $current->save();

            return [$previous, $gallery];
        });

        if ($saved === null) {
            ServiceImageStorage::deleteServiceImages($processed);
            $this->keepPublishedGallery('galeria a fost modificată în timpul procesării');
            return;
        }

        ServiceImageStorage::deleteDisplacedImages(...$saved);
        Storage::delete($this->pendingImages);
    }

    public function failed(?Throwable $exception): void
    {
        $this->keepPublishedGallery('workerul nu a terminat procesarea');
    }

    private function matches(?Service $service): bool
    {
        return $service && (int) $service->user_id === $this->ownerId
            && $service->status === $this->expectedStatus && $service->trashed() === $this->expectedTrashed
            && ServiceImageStorage::galleryRevision(ServiceImageStorage::listingImages($service->images)) === $this->revision;
    }

    private function keepPublishedGallery(string $reason): void
    {
        Log::warning('Service gallery edit skipped.', ['service_id' => $this->serviceId, 'reason' => $reason]);
        Storage::delete($this->pendingImages);
    }
}

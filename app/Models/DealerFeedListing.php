<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DealerFeedListing extends Model
{
    protected $fillable = ['service_id', 'external_id', 'external_key'];

    protected function casts(): array
    {
        return ['feed_withdrawn' => 'bool', 'sync_gallery' => 'array'];
    }

    public static function externalKey(string $externalId): string
    {
        return hash('sha256', $externalId);
    }

    public static function photoFingerprint(array $photos): string
    {
        return hash('sha256', json_encode($photos, JSON_THROW_ON_ERROR));
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(DealerFeedSource::class, 'source_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function trial(): HasOne
    {
        return $this->hasOne(DealerFeedTrial::class, 'listing_id');
    }

    public static function importItem(int $id, int $runId, string $status, bool $updatesGallery = false): Builder
    {
        $query = self::query()->whereKey($id);

        return $updatesGallery
            ? $query->where('sync_gallery->run_id', $runId)->where('sync_gallery->status', $status)
            : $query->where('import_run_id', $runId)->where('import_status', $status);
    }

    public function finishImport(string $status, ?string $error = null, bool $updatesGallery = false): void
    {
        if ($updatesGallery) {
            $gallery = $this->sync_gallery;
            $gallery['status'] = $status;
            $gallery['error'] = $error;
            $this->sync_gallery = $gallery;
            if ($status === 'published') {
                $this->photo_fingerprint = $gallery['fingerprint'];
            }
            $this->save();

            return;
        }
        $this->forceFill(['import_status' => $status, 'import_error' => $error])->save();
    }

    public function publishImport(Service $service): void
    {
        $service->forceFill(['status' => 'active', 'is_active' => true, 'published_at' => now()])->save();
        $this->finishImport('published');
    }
}

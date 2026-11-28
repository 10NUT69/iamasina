<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class DealerFeedRun extends Model
{
    protected $fillable = ['profile', 'status', 'snapshot_path', 'result'];

    protected function casts(): array
    {
        return ['result' => 'array', 'sync_summary' => 'array'];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(DealerFeedSource::class, 'source_id');
    }

    public function importedListings(): HasMany
    {
        return $this->hasMany(DealerFeedListing::class, 'import_run_id');
    }

    public function importedServices(): HasManyThrough
    {
        return $this->hasManyThrough(Service::class, DealerFeedListing::class, 'import_run_id', 'id', 'id', 'service_id');
    }

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('import_status', 'prepared')
            ->whereDoesntHave('importedListings', fn ($items) => $items->whereIn('import_status', ['waiting', 'processing', 'failed']))
            ->whereNotExists(fn ($galleries) => $galleries->selectRaw('1')->from('dealer_feed_listings as galleries')
                ->whereColumn('galleries.source_id', 'dealer_feed_runs.source_id')
                ->whereColumn('galleries.sync_gallery->run_id', 'dealer_feed_runs.id')
                ->whereIn('galleries.sync_gallery->status', ['waiting', 'processing', 'failed']));
    }

    public function scopeWithImportProgress(Builder $query): Builder
    {
        return $query->withCount([
            'importedListings as import_published' => fn ($items) => $items->where('import_status', 'published')
                ->whereHas('service', fn (Builder $services) => $services->withoutTrashed()),
            'importedListings as import_waiting' => fn ($items) => $items->whereIn('import_status', ['waiting', 'processing']),
            'importedListings as import_failed' => fn ($items) => $items->where('import_status', 'failed'),
            'importedListings as import_trashed' => fn ($items) => $items
                ->whereHas('service', fn (Builder $services) => $services->onlyTrashed()),
            'importedServices as import_trashable' => fn (Builder $services) => $services
                ->whereColumn('dealer_feed_listings.source_id', 'dealer_feed_runs.source_id')
                ->where('services.user_id', DealerFeedSource::query()->select('dealer_id')
                    ->whereColumn('dealer_feed_sources.id', 'dealer_feed_runs.source_id')),
        ]);
    }

    public function importLabel(): string
    {
        return match (true) {
            $this->sync_summary !== null && $this->import_status === 'queued' => 'Sincronizare în coadă',
            $this->sync_summary !== null && $this->import_status === 'failed' => 'Sincronizare oprită',
            $this->sync_summary !== null && $this->import_status === 'prepared' => 'Sincronizare aplicată',
            $this->import_status === 'queued' => 'Import în coadă',
            $this->import_status === 'failed' => 'Import oprit',
            $this->import_waiting > 0 => 'Se procesează pozele',
            $this->import_trashed > 0 && ! $this->import_trashable => 'Anunțuri mutate în coș',
            $this->import_failed > 0 => 'Import cu erori la poze',
            default => 'Import finalizat',
        };
    }
}

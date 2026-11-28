<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealerFeedTrial extends Model
{
    protected function casts(): array
    {
        return ['messages' => 'array'];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(DealerFeedListing::class);
    }

    public static function currentRun(int $id, string $runId): ?self
    {
        return self::query()->whereKey($id)->where('run_id', $runId)
            ->whereIn('status', ['queued', 'processing'])->with('listing')->first();
    }

    public function finish(int $processed, array $messages, string $status): void
    {
        $this->forceFill([
            'photo_processed' => $processed,
            'messages' => $messages,
            'status' => $status,
        ])->save();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'buyer_id',
        'seller_id',
        'last_message_at',
        'buyer_deleted_at',
        'seller_deleted_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'buyer_deleted_at' => 'datetime',
        'seller_deleted_at' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function isParticipant(User $user): bool
    {
        return $this->buyer_id === $user->id || $this->seller_id === $user->id;
    }

    public function otherParticipant(User $user): ?User
    {
        if ($this->buyer_id === $user->id) {
            return $this->seller;
        }

        if ($this->seller_id === $user->id) {
            return $this->buyer;
        }

        return null;
    }

    public function isHiddenFor(User $user): bool
    {
        if ($this->buyer_id === $user->id) {
            return $this->buyer_deleted_at !== null;
        }

        if ($this->seller_id === $user->id) {
            return $this->seller_deleted_at !== null;
        }

        return true;
    }

    public function hideFor(User $user): void
    {
        if ($this->buyer_id === $user->id) {
            $this->forceFill(['buyer_deleted_at' => now()])->save();
            return;
        }

        if ($this->seller_id === $user->id) {
            $this->forceFill(['seller_deleted_at' => now()])->save();
        }
    }

    public function restoreForParticipants(): void
    {
        $this->forceFill([
            'buyer_deleted_at' => null,
            'seller_deleted_at' => null,
        ])->save();
    }
}

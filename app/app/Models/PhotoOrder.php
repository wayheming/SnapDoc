<?php
// app/app/Models/PhotoOrder.php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PhotoOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'document_format_id', 'original_path',
        'result_clean_path', 'status', 'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (PhotoOrder $order) {
            $order->uuid       ??= (string) Str::uuid();
            $order->expires_at ??= now()->addHours(24);
        });
    }

    public function documentFormat(): BelongsTo
    {
        return $this->belongsTo(DocumentFormat::class);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<', now());
    }
}

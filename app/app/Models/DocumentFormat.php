<?php
// app/app/Models/DocumentFormat.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class DocumentFormat extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'country', 'width_mm', 'height_mm', 'dpi', 'is_active', 'sort_order',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}

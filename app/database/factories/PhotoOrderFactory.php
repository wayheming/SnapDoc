<?php
// app/database/factories/PhotoOrderFactory.php

namespace Database\Factories;

use App\Models\DocumentFormat;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PhotoOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid'               => (string) Str::uuid(),
            'document_format_id' => DocumentFormat::factory(),
            'original_path'      => 'originals/' . fake()->uuid() . '.png',
            'status'             => 'pending',
            'expires_at'         => now()->addHours(24),
        ];
    }
}

<?php
// app/tests/Unit/Models/PhotoOrderTest.php

use App\Models\PhotoOrder;
use App\Models\DocumentFormat;
use Illuminate\Support\Str;

it('auto-generates uuid and expires_at on creating', function () {
    $format = DocumentFormat::factory()->create();

    $order = PhotoOrder::create([
        'document_format_id' => $format->id,
        'original_path' => 'originals/test.png',
    ]);

    expect($order->uuid)->not->toBeNull()
        ->and(Str::isUuid($order->uuid))->toBeTrue()
        ->and($order->expires_at)->not->toBeNull()
        ->and(now()->diffInHours($order->expires_at))->toBeGreaterThanOrEqual(23);
});

it('scope expired returns only expired orders', function () {
    $format = DocumentFormat::factory()->create();

    PhotoOrder::factory()->create([
        'document_format_id' => $format->id,
        'expires_at' => now()->subHour(),
    ]);
    PhotoOrder::factory()->create([
        'document_format_id' => $format->id,
        'expires_at' => now()->addHour(),
    ]);

    expect(PhotoOrder::expired()->count())->toBe(1);
});

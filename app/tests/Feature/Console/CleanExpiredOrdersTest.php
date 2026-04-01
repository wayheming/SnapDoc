<?php
// app/tests/Feature/Console/CleanExpiredOrdersTest.php

use App\Models\DocumentFormat;
use App\Models\PhotoOrder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

it('deletes expired orders and their files', function () {
    $format = DocumentFormat::factory()->create();

    // Expired order with files
    $expired = PhotoOrder::factory()->create([
        'document_format_id' => $format->id,
        'original_path'      => 'originals/old.png',
        'result_clean_path'  => 'results/old_clean.png',
        'expires_at'         => now()->subHour(),
    ]);
    Storage::put('originals/old.png', 'bytes');
    Storage::put('results/old_clean.png', 'bytes');

    // Active order
    $active = PhotoOrder::factory()->create([
        'document_format_id' => $format->id,
        'original_path'      => 'originals/new.png',
        'expires_at'         => now()->addHours(24),
    ]);
    Storage::put('originals/new.png', 'bytes');

    $this->artisan('orders:clean')->assertSuccessful();

    expect(PhotoOrder::find($expired->id))->toBeNull()
        ->and(PhotoOrder::find($active->id))->not->toBeNull();

    Storage::assertMissing('originals/old.png');
    Storage::assertMissing('results/old_clean.png');
    Storage::assertExists('originals/new.png');
});

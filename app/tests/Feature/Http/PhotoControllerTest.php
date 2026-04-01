<?php
// app/tests/Feature/Http/PhotoControllerTest.php

use App\Models\DocumentFormat;
use App\Models\PhotoOrder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

it('preview returns clean image for valid uuid', function () {
    $format = DocumentFormat::factory()->create();
    $order  = PhotoOrder::factory()->create([
        'document_format_id' => $format->id,
        'status'             => 'completed',
        'result_clean_path'  => 'results/test_clean.png',
        'expires_at'         => now()->addHours(24),
    ]);
    Storage::put('results/test_clean.png', file_get_contents(base_path('tests/fixtures/1x1.png')));

    $this->get(route('preview', $order->uuid))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');
});

it('preview returns 404 for expired order', function () {
    $format = DocumentFormat::factory()->create();
    $order  = PhotoOrder::factory()->create([
        'document_format_id' => $format->id,
        'expires_at'         => now()->subHour(),
    ]);

    $this->get(route('preview', $order->uuid))->assertNotFound();
});

it('download returns clean image for completed order', function () {
    $format = DocumentFormat::factory()->create();
    $order  = PhotoOrder::factory()->create([
        'document_format_id' => $format->id,
        'status'             => 'completed',
        'result_clean_path'  => 'results/test_clean.png',
        'expires_at'         => now()->addHours(24),
    ]);
    Storage::put('results/test_clean.png', file_get_contents(base_path('tests/fixtures/1x1.png')));

    $this->get(route('download', $order->uuid))
        ->assertOk()
        ->assertDownload('photo.png');
});

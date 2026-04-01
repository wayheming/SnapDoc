<?php
// app/tests/Unit/Jobs/ProcessPhotoJobTest.php

use App\Jobs\ProcessPhotoJob;
use App\Models\DocumentFormat;
use App\Models\PhotoOrder;
use App\Services\PhotoProcessorClient;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

it('processes photo: sets completed status and saves clean path', function () {
    $format = DocumentFormat::factory()->create(['width_mm' => 35, 'height_mm' => 45, 'dpi' => 300]);
    $order  = PhotoOrder::factory()->create([
        'document_format_id' => $format->id,
        'original_path'      => 'originals/test.png',
        'status'             => 'pending',
    ]);

    Storage::put('originals/test.png', 'fake-image-bytes');

    $fakeCleanBytes = file_get_contents(base_path('tests/fixtures/1x1.png'));

    $mockClient = Mockery::mock(PhotoProcessorClient::class);

    $mockClient->shouldReceive('process')
        ->once()
        ->with('fake-image-bytes', 35, 45, 300)
        ->andReturn($fakeCleanBytes);

    $job = new ProcessPhotoJob($order);
    $job->handle($mockClient);

    $order->refresh();
    expect($order->status)->toBe('completed')
        ->and($order->result_clean_path)->toBe("results/{$order->uuid}_clean.png");

    Storage::assertExists("results/{$order->uuid}_clean.png");
});

it('sets status failed when processor throws', function () {
    $format = DocumentFormat::factory()->create();
    $order  = PhotoOrder::factory()->create([
        'document_format_id' => $format->id,
        'original_path'      => 'originals/test.png',
    ]);

    Storage::put('originals/test.png', 'bytes');

    $mockClient = Mockery::mock(PhotoProcessorClient::class);

    $mockClient->shouldReceive('process')->andThrow(new \RuntimeException('Processor down'));

    $job = new ProcessPhotoJob($order);

    expect(fn () => $job->handle($mockClient))
        ->toThrow(\RuntimeException::class);

    expect($order->fresh()->status)->toBe('failed');
});

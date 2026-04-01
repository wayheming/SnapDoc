<?php
// app/tests/Feature/Livewire/PhotoProcessorTest.php

use App\Jobs\ProcessPhotoJob;
use App\Livewire\PhotoProcessor;
use App\Models\DocumentFormat;
use App\Models\PhotoOrder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
    Queue::fake();
});

it('renders step 1 with document formats', function () {
    DocumentFormat::factory()->create(['name' => 'UA Passport', 'is_active' => true]);

    Livewire::test(PhotoProcessor::class)
        ->assertSee('UA Passport')
        ->assertSet('step', 1);
});

it('submits photo: creates order, dispatches job, moves to step 2', function () {
    $format = DocumentFormat::factory()->create();
    $photo  = UploadedFile::fake()->image('portrait.jpg', 400, 500);

    Livewire::test(PhotoProcessor::class)
        ->set('documentFormatId', $format->id)
        ->set('privacyAccepted', true)
        ->set('photo', $photo)
        ->call('submit')
        ->assertSet('step', 2)
        ->assertSet('orderUuid', fn ($uuid) => $uuid !== null);

    expect(PhotoOrder::count())->toBe(1);
    Queue::assertPushed(ProcessPhotoJob::class);
});

it('validation rejects missing photo', function () {
    $format = DocumentFormat::factory()->create();

    Livewire::test(PhotoProcessor::class)
        ->set('documentFormatId', $format->id)
        ->set('privacyAccepted', true)
        ->call('submit')
        ->assertHasErrors(['photo' => 'required']);
});

it('validation rejects unchecked privacy policy', function () {
    $format = DocumentFormat::factory()->create();
    $photo  = UploadedFile::fake()->image('photo.jpg', 400, 500);

    Livewire::test(PhotoProcessor::class)
        ->set('documentFormatId', $format->id)
        ->set('privacyAccepted', false)
        ->set('photo', $photo)
        ->call('submit')
        ->assertHasErrors(['privacyAccepted']);
});

it('checkStatus moves nothing until completed', function () {
    $format = DocumentFormat::factory()->create();
    $order  = PhotoOrder::factory()->create([
        'document_format_id' => $format->id,
        'status'             => 'processing',
    ]);

    Livewire::test(PhotoProcessor::class)
        ->set('step', 2)
        ->set('orderUuid', $order->uuid)
        ->call('checkStatus')
        ->assertSet('step', 2);
});

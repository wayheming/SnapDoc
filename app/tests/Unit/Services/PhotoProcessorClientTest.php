<?php
// app/tests/Unit/Services/PhotoProcessorClientTest.php

use App\Services\PhotoProcessorClient;
use Illuminate\Support\Facades\Http;

it('sends multipart POST with photo and dimension params', function () {
    Http::fake([
        'http://processor:8000/process' => Http::response(
            file_get_contents(base_path('tests/fixtures/1x1.png')),
            200,
            ['Content-Type' => 'image/png']
        ),
    ]);

    $client = new PhotoProcessorClient('http://processor:8000');
    $result = $client->process('fake-image-bytes', 35, 45, 300);

    expect($result)->toBeString()->not->toBeEmpty();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/process')
            && $request->isMultipart();
    });
});

it('throws RuntimeException when processor returns error', function () {
    Http::fake([
        'http://processor:8000/process' => Http::response('error', 500),
    ]);

    $client = new PhotoProcessorClient('http://processor:8000');

    expect(fn () => $client->process('bytes', 35, 45, 300))
        ->toThrow(\RuntimeException::class);
});

<?php
// app/app/Services/PhotoProcessorClient.php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PhotoProcessorClient
{
    public function __construct(
        private readonly string $baseUrl = 'http://processor:8000'
    ) {}

    public function process(string $imageBytes, int $widthMm, int $heightMm, int $dpi): string
    {
        $response = Http::attach('photo', $imageBytes, 'photo.png', ['Content-Type' => 'image/png'])
            ->post("{$this->baseUrl}/process", [
                'width_mm'  => (string) $widthMm,
                'height_mm' => (string) $heightMm,
                'dpi'       => (string) $dpi,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException("Photo processor returned HTTP {$response->status()}");
        }

        return $response->body();
    }
}

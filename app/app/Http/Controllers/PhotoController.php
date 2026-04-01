<?php
// app/app/Http/Controllers/PhotoController.php

namespace App\Http\Controllers;

use App\Models\PhotoOrder;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhotoController extends Controller
{
    public function preview(string $uuid): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $order = PhotoOrder::where('uuid', $uuid)
            ->where('expires_at', '>', now())
            ->whereNotNull('result_clean_path')
            ->firstOrFail();

        $path = Storage::path($order->result_clean_path);

        return response()->file($path, ['Content-Type' => 'image/png']);
    }

    public function download(string $uuid): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $order = PhotoOrder::where('uuid', $uuid)
            ->where('expires_at', '>', now())
            ->whereNotNull('result_clean_path')
            ->firstOrFail();

        $path = Storage::path($order->result_clean_path);

        return response()->download($path, 'photo.png', ['Content-Type' => 'image/png']);
    }
}

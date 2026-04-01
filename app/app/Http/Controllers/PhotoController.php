<?php
// app/app/Http/Controllers/PhotoController.php

namespace App\Http\Controllers;

use App\Models\PhotoOrder;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhotoController extends Controller
{
    public function preview(string $uuid): \Illuminate\Http\Response
    {
        $order = PhotoOrder::where('uuid', $uuid)
            ->where('expires_at', '>', now())
            ->whereNotNull('result_clean_path')
            ->firstOrFail();

        $bytes = Storage::get($order->result_clean_path);

        return response($bytes, 200, ['Content-Type' => 'image/png']);
    }

    public function download(string $uuid): StreamedResponse
    {
        $order = PhotoOrder::where('uuid', $uuid)
            ->where('expires_at', '>', now())
            ->whereNotNull('result_clean_path')
            ->firstOrFail();

        return Storage::download($order->result_clean_path, 'photo.png');
    }
}

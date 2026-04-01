<?php
// app/app/Jobs/ProcessPhotoJob.php

namespace App\Jobs;

use App\Models\PhotoOrder;
use App\Services\PhotoProcessorClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessPhotoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(public readonly PhotoOrder $order) {}

    public function handle(PhotoProcessorClient $client): void
    {
        $this->order->update(['status' => 'processing']);

        try {
            $format        = $this->order->documentFormat;
            $originalBytes = Storage::get($this->order->original_path);

            $cleanBytes = $client->process(
                $originalBytes,
                $format->width_mm,
                $format->height_mm,
                $format->dpi,
            );

            $cleanPath = "results/{$this->order->uuid}_clean.png";
            Storage::put($cleanPath, $cleanBytes);

            $this->order->update([
                'status'            => 'completed',
                'result_clean_path' => $cleanPath,
            ]);
        } catch (\Throwable $e) {
            $this->order->update(['status' => 'failed']);
            throw $e;
        }
    }
}

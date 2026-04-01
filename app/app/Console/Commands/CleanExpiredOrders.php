<?php
// app/app/Console/Commands/CleanExpiredOrders.php

namespace App\Console\Commands;

use App\Models\PhotoOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanExpiredOrders extends Command
{
    protected $signature   = 'orders:clean';
    protected $description = 'Delete expired photo orders and their files';

    public function handle(): int
    {
        $count = 0;

        PhotoOrder::expired()->chunkById(100, function ($orders) use (&$count) {
            foreach ($orders as $order) {
                foreach ([
                    $order->original_path,
                    $order->result_clean_path,
                ] as $path) {
                    if ($path && Storage::exists($path)) {
                        Storage::delete($path);
                    }
                }
                $order->delete();
                $count++;
            }
        });

        $this->info("Deleted {$count} expired orders.");
        return Command::SUCCESS;
    }
}

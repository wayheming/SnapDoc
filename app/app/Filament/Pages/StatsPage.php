<?php

namespace App\Filament\Pages;

use App\Models\PhotoOrder;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class StatsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Statistics';

    protected string $view = 'filament.pages.stats-page';

    protected function getViewData(): array
    {
        return [
            'total'      => PhotoOrder::count(),
            'today'      => PhotoOrder::whereDate('created_at', today())->count(),
            'this_week'  => PhotoOrder::where('created_at', '>=', now()->startOfWeek())->count(),
            'this_month' => PhotoOrder::where('created_at', '>=', now()->startOfMonth())->count(),
            'failed'     => PhotoOrder::where('status', 'failed')->count(),
        ];
    }
}

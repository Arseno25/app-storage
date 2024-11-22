<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\Status;
use App\Models\File;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $counts = File::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->mapWithKeys(fn($count, $status) => [(string)$status => $count])
            ->toArray();

        return [
            Stat::make('Uploaded', $counts[(string)Status::Uploaded->value] ?? 0),
            Stat::make('Revisi', $counts[(string)Status::Revisi->value] ?? 0),
            Stat::make('Approve', $counts[(string)Status::Approve->value] ?? 0),
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use App\Enums\Status;
use App\Models\File;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsOverview extends BaseWidget
{

    protected $__name = 'User Stats';
    protected function getStats(): array
    {
        $user = auth()->user();

        $query = File::query();

        if (!$user->hasRole('super_admin')) {
            $query->where('user_id', $user->id);
        }

        $counts = $query
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->mapWithKeys(fn($count, $status) => [(string)$status => $count])
            ->toArray();

        return [
            Stat::make('Pending', $counts[(string)Status::Pending->value] ?? 0),
            Stat::make('Revised', $counts[(string)Status::Revised->value] ?? 0),
            Stat::make('Complete', $counts[(string)Status::Completed->value] ?? 0),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->hasRole('users');
    }
}

<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

use ShuvroRoy\FilamentSpatieLaravelHealth\Pages\HealthCheckResults as BaseHealthCheckResults;

class Health extends BaseHealthCheckResults
{
    protected static ?string $navigationIcon = 'heroicon-o-heart';

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Health Check';
    }

    public static function getNavigationLabel(): string
    {
        return 'App Health';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }
}

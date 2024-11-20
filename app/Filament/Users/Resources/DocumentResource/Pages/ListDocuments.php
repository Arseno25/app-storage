<?php

namespace App\Filament\Users\Resources\DocumentResource\Pages;

use App\Filament\Users\Resources\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->hidden(function () {
                    return Auth::user()->hasRole('users');
                }),
        ];
    }
}

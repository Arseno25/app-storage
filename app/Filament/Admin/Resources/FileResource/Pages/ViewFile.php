<?php

namespace App\Filament\Admin\Resources\FileResource\Pages;

use App\Enums\Status;
use App\Filament\Admin\Resources\FileResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;
use Joaopaulolndev\FilamentPdfViewer\Infolists\Components\PdfViewerEntry;
use ZipArchive;

class ViewFile extends ViewRecord
{
    protected static string $resource = FileResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }


    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->description('User Information')
                    ->schema([
                    TextEntry::make('user.name'),
                    TextEntry::make('description'),
                    TextEntry::make('status')
                        ->badge()
                        ->color(function () {
                            return match ($this->record->status) {
                                Status::Uploaded->value => 'primary',
                                Status::Revisi->value => 'warning',
                                Status::Approve->value => 'success',
                            };
                        }),
                    ]),
                Section::make()
                    ->description('File Information')
                    ->schema([
                TextEntry::make('document_word'),
                PdfViewerEntry::make('document_pdf')
                    ->label('View the PDF')
                    ->minHeight('60svh')
                    ->fileUrl(Storage::url($this->record->document_pdf))
                    ->columnSpanFull(),
                        ])
            ]);
    }
}

<?php

namespace App\Filament\Resources\FileResource\Pages;

use App\Enums\Status;
use App\Filament\Resources\FileResource;
use App\Models\File;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Storage;
use Joaopaulolndev\FilamentPdfViewer\Infolists\Components\PdfViewerEntry;
use ZipArchive;

class ViewFile extends ViewRecord
{
    protected static string $resource = FileResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('downloads')
                ->label('Download File')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray')
                ->button()
                ->action(function () {
                    $paths = [
                        'pdf' => public_path('storage/' . $this->record->document_pdf),
                        'word' => public_path('storage/' . $this->record->document_word),
                    ];

                    // Validasi file
                    foreach ($paths as $type => $path) {
                        if (!file_exists($path)) {
                            return Notification::make()
                                ->title("File " . strtoupper($type) . " tidak ditemukan")
                                ->danger()
                                ->send();
                        }
                    }

                    // Nama file ZIP
                    $zipFileName = sprintf(
                        '%s_%s_%s.zip',
                        $this->record->title,
                        $this->record->status,
                        now()->timestamp
                    );

                    // Path penyimpanan sementara
                    $tempDir = public_path('storage/temp');
                    $zipPath = "{$tempDir}/{$zipFileName}";

                    // Buat folder 'temp' jika belum ada
                    if (!is_dir($tempDir)) {
                        mkdir($tempDir, 0755, true);
                    }

                    // Buat file ZIP
                    $zip = new ZipArchive();
                    if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                        foreach ($paths as $path) {
                            $zip->addFile($path, basename($path));
                        }
                        $zip->close();
                    } else {
                        return Notification::make()
                            ->title('Gagal membuat file ZIP')
                            ->danger()
                            ->send();
                    }

                    Notification::make()
                        ->title('File ZIP berhasil diunduh: ' . $zipFileName)
                        ->success()
                        ->send();

                    return response()->download($zipPath)->deleteFileAfterSend(true);
                }),
            Actions\EditAction::make()
                ->disabled(fn() => $this->record->status === Status::Approved->value || $this->record->status === Status::Completed->value)
            ->label(auth()->user()->hasRole('users') ? 'Need Revisi' : 'Edit'),
            Action::make('approve')
                ->color(Color::Fuchsia)
            ->label('Approve')
                ->disabled(fn() => $this->record->status === Status::Approved->value || $this->record->status === Status::Completed->value)
            ->action(fn (File $file) => $file->update([
                'status' => Status::Approved->value
            ]))
        ];
    }


    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->description('User Information')
                    ->columns(2)
                    ->schema([
                    TextEntry::make('user.name'),
                    TextEntry::make('title'),
                    TextEntry::make('description'),
                    TextEntry::make('status')
                        ->badge()
                        ->color(function () {
                            return match ($this->record->status) {
                                Status::Pending->value => 'primary',
                                Status::Revisi->value => 'warning',
                                Status::Revised->value => Color::Orange,
                                Status::Approved->value => Color::Fuchsia,
                                Status::Completed->value => 'success',
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

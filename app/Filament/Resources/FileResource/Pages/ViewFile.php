<?php

namespace App\Filament\Resources\FileResource\Pages;

use App\Enums\Status;
use App\Filament\Resources\FileResource;
use App\Models\File;
use Carbon\Carbon;
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
                ->color(Color::Violet)
                ->icon('heroicon-o-arrow-down-tray')
                ->button()
                ->action(function () {
                    // Path folder berdasarkan slug dari title
                    $baseDir = storage_path('app/public/documents/' . \Str::slug($this->record->title));
                    $paths = [
                        'pdf' => "{$baseDir}/" . basename($this->record->document_pdf),
                        'word' => "{$baseDir}/" . basename($this->record->document_word),
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
                        \Str::slug($this->record->title),
                        $this->record->status,
                        now()->timestamp
                    );

                    // Path penyimpanan sementara
                    $tempDir = storage_path('app/public/temp');
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
                        ->title('File ZIP berhasil dibuat: ' . $zipFileName)
                        ->success()
                        ->send();

                    return response()->download($zipPath)->deleteFileAfterSend(true);
                }),
            Actions\ActionGroup::make([
                Actions\EditAction::make()
                    ->disabled(function () {
                        return  ($this->record->status === Status::Approved->value || $this->record->status === Status::Completed->value);
                    })
                    ->label(function () {
                        return auth()->user()->hasRole('users') ? 'Need Revisi' : 'Edit';
                    }),
                Action::make('approve')
                    ->color(Color::Fuchsia)
                    ->label('Approve')
                    ->icon('heroicon-o-star')
                    ->hidden(fn() => auth()->user()->hasRole('super_admin'))
                    ->disabled(fn() => $this->record->status === Status::Approved->value || $this->record->status === Status::Completed->value)
                    ->action(fn (File $file) => $file->update([
                        'status' => Status::Approved->value
                    ])),
                Action::make('Completed')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->label('Completed')
                    ->hidden(fn() => auth()->user()->hasRole('users'))
                    ->disabled(fn() => $this->record->status === Status::Completed->value)
                    ->action(fn (File $file) => $file->update([
                        'status' => Status::Completed->value,
                        'completed_at' => now(),
                    ]))
            ]),
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
                        TextEntry::make('completed_at')
                            ->label('File will be deleted after status completion')
                            ->columnSpanFull()
                            ->color(Color::Red)
                            ->formatStateUsing(function ($state) {
                                $deletionDate = Carbon::parse($state)->addDays(6)->endOfDay();
                                $now = Carbon::now();
                                if ($now->greaterThanOrEqualTo($deletionDate)) {
                                    return 'Your file has been deleted';
                                }
                                $deletionDateFormatted = $deletionDate->format('d-m-Y \a\t h:i A');
                                return "{$deletionDateFormatted} (" . $deletionDate->diffForHumans($now, true) . " left)";
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

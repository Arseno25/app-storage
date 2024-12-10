<?php

namespace App\Filament\Resources;

use App\Enums\Status;
use App\Filament\Resources\FileResource\RelationManagers;
use App\Models\File;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;

class FileResource extends Resource
{
    protected static ?string $model = File::class;

    protected static ?string $navigationGroup = 'File Management';

    protected static ?string $navigationLabel = 'Documents';

    protected static ?int $navigationSort = -4;

    protected static ?string $navigationIcon = 'heroicon-o-paper-clip';

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            // Super Admin: Hitung semua file dengan status 'Revisi'
            $revisiCount = static::getModel()::where('status', Status::Revisi)->count();

            return $revisiCount > 0 ? (string) $revisiCount : null;
        } else {
            // User biasa: Hitung file dengan status 'Pending' atau 'Revised' yang terkait dengan user ini
            $pendingCount = static::getModel()::where('status', Status::Pending)
                ->where('user_id', $user->id) // Filter berdasarkan user_id
                ->count();

            $revisedCount = static::getModel()::where('status', Status::Revised)
                ->where('user_id', $user->id) // Filter berdasarkan user_id
                ->count();

            return $pendingCount > 0 || $revisedCount > 0 ? (string) ($pendingCount + $revisedCount) : null;
        }
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            $revisiCount = static::getModel()::where('status', Status::Revisi)->count();

            return $revisiCount > 0 ? 'warning' : null;
        } else {
            $pendingCount = static::getModel()::where('status', Status::Pending)
                ->where('user_id', $user->id)
                ->count();

            $revisedCount = static::getModel()::where('status', Status::Revised)
                ->where('user_id', $user->id)
                ->count();

            if ($pendingCount > 0) {
                return 'primary';
            } elseif ($revisedCount > 0) {
                return 'warning';
            }
        }

        return null;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Receiver')
                    ->required()
                    ->preload()
                    ->disabled(!auth()->user()->hasRole(['super_admin', 'admin', 'Super Admin', 'Admin']))
                    ->placeholder(auth()->user()->hasRole('users') ? 'Select Admin' : 'Select User')
                    ->relationship('user', 'name', function ($query) {
                            $query->whereHas('roles', function ($q) {
                                $q->where('name', 'users');
                            });
                    }),
                Forms\Components\Select::make('admin_id')
                    ->label('Sender')
                    ->required()
                    ->preload()
                    ->disabled(!auth()->user()->hasRole(['super_admin', 'admin', 'Super Admin', 'Admin']))
                    ->default(auth()->user()->hasRole('super_admin') ? auth()->id() :  auth()->user()->file()->first()->admin_id)
                    ->placeholder('Select Sender')
                    ->relationship('userAdmin', 'name', function ($query) {
                          $query->whereHas('roles', function ($q) {
                                $q->whereIn('name', ['super_admin', 'admin', 'Super Admin', 'Admin']);
                            });
                    }),
                Forms\Components\TextInput::make('title')
                    ->label('Document Title')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->label('Description'),
                Forms\Components\Select::make('status')
                    ->required()
                    ->preload()
                    ->default(fn() => auth()->user()->hasRole('users') ? Status::Revisi->value :   Status::Pending->value)
                    ->options(function () {
                        if(auth()->user()->hasRole('users')) {
                            return [
                                Status::Revisi->value => Status::Revisi->label(),
                            ];
                        }
                        return [
                            Status::Pending->value => Status::Pending->label(),
                            Status::Revised->value => Status::Revised->label(),
                        ];

                    }),
                Forms\Components\FileUpload::make('document_word')
                    ->required()
                    ->label('File Word')
                    ->preserveFilenames()
                    ->directory(fn (\Filament\Forms\Get $get) => 'documents/' . \Str::slug($get('title')))
                    ->acceptedFileTypes(['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']),
                Forms\Components\FileUpload::make('document_pdf')
                    ->required()
                    ->label('File PDF')
                    ->preserveFilenames()
                    ->directory(fn (\Filament\Forms\Get $get) => 'documents/' . \Str::slug($get('title')))
                    ->acceptedFileTypes(['application/pdf']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup('status', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Receiver'),
                Tables\Columns\TextColumn::make('title')
                    ->label('Document Title'),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('document_word')
                ->limit(20),
                Tables\Columns\TextColumn::make('document_pdf')
                    ->limit(20),
                Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn ($state) => match ($state) {
                    Status::Pending->value => 'primary',
                    Status::Revisi->value => 'warning',
                    Status::Revised->value => Color::Orange,
                    Status::Approved->value => Color::Fuchsia,
                    Status::Completed->value => 'success',
                }),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('File will be deleted after completion')
                    ->description('Download your file now before 6 days')
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
            ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('approve')
                        ->color(Color::Fuchsia)
                        ->label('Approve')
                        ->icon('heroicon-o-star')
                        ->disabled(fn($record) => $record->status === Status::Approved->value || $record->status === Status::Completed->value)
                        ->action(fn (File $file) => $file->update([
                            'status' => Status::Approved->value
                        ]))
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\FileResource\Pages\ListFiles::route('/'),
            'create' => \App\Filament\Resources\FileResource\Pages\CreateFile::route('/create'),
            'edit' => \App\Filament\Resources\FileResource\Pages\EditFile::route('/{record}/edit'),
            'view' => \App\Filament\Resources\FileResource\Pages\ViewFile::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->when(
            !auth()->user()->hasRole('super_admin'),
            function ($query) {
                $query->whereHas('user', function ($query) {
                    $query->where('id', auth()->id());
                });
            }
        );
    }
}

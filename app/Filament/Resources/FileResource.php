<?php

namespace App\Filament\Resources;

use App\Enums\Status;
use App\Filament\Resources\FileResource\RelationManagers;
use App\Models\File;
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

    protected static ?int $navigationSort = -4;

    public static function getNavigationBadge(): ?string
    {
        $revisiCount = static::getModel()::where('status', Status::Revisi)->count();

        return $revisiCount > 0 ? (string) $revisiCount : null;
    }
    public static function getNavigationBadgeColor(): ?string
    {
        $revisiCount = static::getModel()::where('status', Status::Revisi)->count();

        return $revisiCount > 0 ? 'warning' : 'primary';
    }
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->required()
                    ->preload()
                    ->placeholder(auth()->user()->hasRole('users') ? 'Select Admin atau Super Admin' : null)
                    ->relationship('user', 'name', function ($query) {
                        if (auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('admin')) {
                            $query->whereHas('roles', function ($q) {
                                $q->where('name', 'users');
                            });
                        } elseif (auth()->user()->hasRole('users')) {
                            $query->whereHas('roles', function ($q) {
                                $q->whereIn('name', ['super_admin', 'admin']);
                            });
                        }
                    }),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->label('Description'),
                Forms\Components\Select::make('status')
                    ->required()
                    ->preload()
                    ->options(function () {
                        if(auth()->user()->hasRole('users')) {
                            return [
                                Status::Revisi->value => Status::Revisi->label(),
                                Status::Approved->value => Status::Approved->label(),
                                Status::Completed->value => Status::Completed->label(),
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
                    ->acceptedFileTypes(['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']),
                Forms\Components\FileUpload::make('document_pdf')
                    ->label('File PDF')
                    ->required()
                    ->preserveFilenames()
                    ->acceptedFileTypes(['application/pdf']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name'),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('document_word'),
                Tables\Columns\TextColumn::make('document_pdf'),
                Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn ($state) => match ($state) {
                    Status::Pending->value => 'primary',
                    Status::Revisi->value => 'warning',
                    Status::Revised->value => Color::Orange,
                    Status::Approved->value => Color::Gray,
                    Status::Completed->value => 'success',
                }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
        return parent::getEloquentQuery()
            ->whereHas('user', function ($query) {
                $query->where('id', auth()->id());
            });
    }
}

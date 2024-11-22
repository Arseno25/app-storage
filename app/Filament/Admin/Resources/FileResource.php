<?php

namespace App\Filament\Admin\Resources;

use App\Enums\Status;
use App\Filament\Resources\FileResource\RelationManagers;
use App\Models\File;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
                    ->searchable()
                    ->preload()
                    ->relationship('user', 'name', function($query){
                        if (auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('admin')) {
                            $query->whereHas('roles', function ($q) {
                                $q->where('name', 'users');
                            });
                        }
                    }),
                Forms\Components\Textarea::make('description')
                    ->label('Description'),
                Forms\Components\Select::make('status')
                    ->options(function () {
                        if(auth()->user()->hasRole('users')) {
                            return [
                                Status::Revisi->value => Status::Revisi->label(),
                                Status::Approve->value => Status::Approve->label(),
                            ];
                        }
                        return [
                            Status::Uploaded->value => Status::Uploaded->label(),
                            Status::Revisi->value => Status::Revisi->label(),
                            Status::Approve->value => Status::Approve->label(),
                        ];

                    }),
                Forms\Components\FileUpload::make('document_word')
                    ->label('File Word')
                    ->preserveFilenames()
                    ->acceptedFileTypes(['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']),
                Forms\Components\FileUpload::make('document_pdf')
                    ->label('File PDF')
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
                    Status::Uploaded->value => 'primary',
                    Status::Revisi->value => 'warning',
                    Status::Approve->value => 'success',
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
            'index' => \App\Filament\Admin\Resources\FileResource\Pages\ListFiles::route('/'),
            'create' => \App\Filament\Admin\Resources\FileResource\Pages\CreateFile::route('/create'),
            'edit' => \App\Filament\Admin\Resources\FileResource\Pages\EditFile::route('/{record}/edit'),
            'view' => \App\Filament\Admin\Resources\FileResource\Pages\ViewFile::route('/{record}'),
        ];
    }
}

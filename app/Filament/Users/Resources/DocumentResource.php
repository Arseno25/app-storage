<?php

namespace App\Filament\Users\Resources;

use App\Enums\Status;
use App\Filament\Users\Resources\DocumentResource\Pages;
use App\Filament\Users\Resources\DocumentResource\RelationManagers;
use App\Models\File;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class DocumentResource extends Resource
{
    protected static ?string $model = File::class;

    protected static ?string $navigationLabel = 'Document';

    protected static ?string $navigationIcon = 'heroicon-o-paper-clip';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->searchable()
                    ->preload()
                    ->relationship('user', 'name', function($query){
                        if (auth()->user()->hasRole('users')) {
                            $query->whereHas('roles', function ($q) {
                                $q->where('name', 'super_admin') || $q->where('name', 'admin');
                            });
                        }
                    }),
                Forms\Components\Textarea::make('description')
                    ->label('Description'),
                Forms\Components\Select::make('status')
                    ->options([
                        Status::Uploaded->value => Status::Uploaded->label(),
                        Status::Revisi->value => Status::Revisi->label(),
                        Status::Approve->value => Status::Approve->label(),
                    ]),
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
                    Tables\Actions\DeleteBulkAction::make()
                        ->hidden( function () {
                            return Auth::user()->hasRole('users');
                        }),
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
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
            'view' => Pages\ViewDocument::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
        ->whereHas('user', function ($query) {
            $query->where('id', auth()->id());
        });
    }
}

<?php

namespace App\Filament\Resources;

use AllowDynamicProperties;
use App\Enums\ProjectStatus;
use App\Enums\Status;
use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use IbrahimBougaoua\FilaProgress\Tables\Columns\ProgressBar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

#[AllowDynamicProperties] class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench';

    protected static ?string $navigationGroup = 'File Management';

    protected static ?string $navigationLabel = 'Projects';


    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Project Title')
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->label('Receiver')
                    ->required()
                    ->preload()
                    ->disabled(!auth()->user()->hasRole(['super_admin', 'admin', 'Super Admin', 'Admin']))
                    ->placeholder( 'Select User')
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
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->required()
                    ->options([
                       ProjectStatus::Pending->value => ProjectStatus::Pending->label(),
                       ProjectStatus::Onprogress->value => ProjectStatus::Onprogress->label(),
                       ProjectStatus::Completed->value => ProjectStatus::Completed->label(),
                    ])
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state === ProjectStatus::Onprogress->value) {
                            $set('project_progress_disabled', false);
                        } else {
                            $set('project_progress_disabled', true);
                        }
                    }),
                Forms\Components\TextInput::make('total_target')
                    ->label('Target Project')
                    ->numeric()
                    ->default(100)
                    ->hidden( auth()->user()->hasRole('users'))
                    ->disabled()
                    ->required(),
                Forms\Components\TextInput::make('project_progress')
                    ->label('Project Progress')
                    ->hidden( auth()->user()->hasRole('users'))
                    ->disabled(fn ($get) => $get('project_progress_disabled') ?? $get('status') !== ProjectStatus::Onprogress->value)
                    ->numeric()
                    ->suffix('%')
                    ->required(),
                SpatieMediaLibraryFileUpload::make('image')
                    ->collection('project_image')
                    ->multiple()
                    ->columnSpanFull()
                    ->label('Image')
                    ->required(),
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('image')
                    ->label('Image')
                    ->collection('project_image')
                    ->circular(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Project Title')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Receiver')
                    ->sortable(),
                Tables\Columns\TextColumn::make('userAdmin.name')
                    ->label('Sender')
                    ->badge()
                    ->sortable(),
                ProgressBar::make('project_progress')
                    ->getStateUsing(function ($record) {
                        $total = $record->total_target;
                        $progress = $record->project_progress;
                        return [
                            'total' => $total,
                            'progress' => $progress,
                        ];
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color( fn( $record ) => match ( $record->status ) {
                        ProjectStatus::Pending->value => 'primary',
                        ProjectStatus::Onprogress->value => 'warning',
                        ProjectStatus::Completed->value => 'success',
                    })
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
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

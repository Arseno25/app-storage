<?php

namespace App\Filament\Resources\FileResource\Pages;

use App\Filament\Resources\FileResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditFile extends EditRecord
{
    protected static string $resource = FileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $userId = $this->data['admin_id'];
        $user = User::find($userId);

        Notification::make()
            ->title('New File update successfully')
            ->body('You have one document to check with status ' . $this->data['status'] . ': '.$this->data['title'].' - '.$this->data['description'])
            ->warning()
            ->sendToDatabase($user);
    }
}

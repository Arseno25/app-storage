<?php

namespace App\Filament\Users\Resources\DocumentResource\Pages;

use App\Filament\Users\Resources\DocumentResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
            ->hidden( function () {
                return Auth::user()->hasRole('users');
            }),
        ];
    }

    protected function afterSave(): void
    {
        $userId = $this->data['user_id'];
        $user = User::find($userId);
        $senderNumber = Auth::user()->phone_number;

        $sender = Auth::user()->name;

        $phoneNumber = $user->phone_number;

        $recordId = is_array($this->record) ? $this->record['id'] : $this->record->id;

        $message = sprintf(
            "Halo *%s*,\n\n%s telah mengupdate dokumen dengan status: *%s*.
            \n\nSegera cek dokumen yang telah diupdate.\n\nUrl: %s",
            $user->name,
            $sender,
            $this->data['status'],
            ENV('APP_URL').'/users/document/'. $recordId
        );

        Notification::make()
            ->title('New File Uploaded successfully')
            ->body($this->data['description'])
            ->warning()
            ->sendToDatabase($user);

        $this->sendWhatsAppNotification($phoneNumber, $message, $senderNumber);
    }

    protected function sendWhatsAppNotification(string $phoneNumber, string $message, string $senderNumber): void
    {
        $apiUrl = "https://mpwa.senotekno.com/send-message";

        $data = [
            'api_key' => 'P8eJZbJpZTIjVdClfV0H7ZCzFSoNvG',
            'sender' => $senderNumber,
            'number' => $phoneNumber,
            'message' => $message,
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

    }
}

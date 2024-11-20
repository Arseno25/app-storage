<?php

namespace App\Filament\Admin\Resources\FileResource\Pages;

use App\Filament\Admin\Resources\FileResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\NoReturn;

class CreateFile extends CreateRecord
{
    protected static string $resource = FileResource::class;


    #[NoReturn]
    protected function afterCreate(): void
    {
        $userId = $this->data['user_id'];
        $user = User::find($userId);
        $senderNumber = Auth::user()->phone_number;

        $sender = Auth::user()->name;

        $phoneNumber = $user->phone_number;

        $recordId = is_array($this->record) ? $this->record['id'] : $this->record->id;

        $message = sprintf(
            "Halo *%s*,\n\n%s telah mengunggah dokumen baru dengan status: *%s*.
            \n\nSegera cek dokumen baru yang telah diunggah.\n\nUrl: %s",
            $user->name,
            $sender,
            $this->data['status'],
            ENV('APP_URL').'/admin/files/'. $recordId
        );

        Notification::make()
            ->title('New File Uploaded successfully')
            ->body('Test')
            ->sendToDatabase($user);

        $this->sendWhatsAppNotification($phoneNumber, $message, $senderNumber);

    }

    #[NoReturn]
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

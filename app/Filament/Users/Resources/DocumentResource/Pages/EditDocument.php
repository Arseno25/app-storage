<?php

namespace App\Filament\Users\Resources\DocumentResource\Pages;

use App\Filament\Users\Resources\DocumentResource;
use App\Models\User;
use Filament\Actions;
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

    protected function beforeSave(): void
    {
        $userId = $this->data['user_id'];
        $user = User::find($userId);
        $sender = Auth::user()->name;

        $phoneNumber = $user->phone_number;

        $message = `Halo {$user->name}, {$sender} telah menguload dokumen baru dengan status {$this->data['status']}.
        Segera cek dokumen baru yang telah diupload.`;

        $this->sendWhatsAppNotification($phoneNumber, $message, $sender);
    }

    protected function sendWhatsAppNotification(string $phoneNumber, string $message, string $sender): void
    {
        $apiUrl = "https://gateway.senodev.com/send-message";

        try {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
            ])->post($apiUrl, [
                'api_key'  => 'CUKRLn1gbfIoRSiVDkEIIleYk3thvp',
                'sender' => $sender,
                'phone'   => $phoneNumber,
                'message' => $message,
            ]);

            if ($response->failed()) {
                throw new \Exception('Gagal mengirim notifikasi WhatsApp: ' . $response->body());
            }
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }

    }
}

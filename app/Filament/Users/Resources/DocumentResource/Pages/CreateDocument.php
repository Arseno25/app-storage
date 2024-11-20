<?php

namespace App\Filament\Users\Resources\DocumentResource\Pages;

use App\Filament\Users\Resources\DocumentResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;


    protected function beforeCreate(): void
    {
        $userId = $this->data['user_id'];
        $user = User::find($userId);

        $phoneNumber = $user->phone_number;

        dd($phoneNumber);

        if (!$user || !$user->phone_number) {
            throw new \Exception('Nomor telepon tidak ditemukan untuk pengguna yang dipilih.');
        }

    }
}

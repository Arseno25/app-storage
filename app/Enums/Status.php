<?php

namespace App\Enums;

enum Status: string
{
    case Pending = 'pending'; // File baru diunggah, menunggu review.
    case Revisi = 'revisi';   // User meminta revisi.
    case Revised = 'revised'; // File revisi diunggah.
    case Approved = 'approved'; // User menyetujui file.
    case Completed = 'completed'; // Proses selesai.

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'pending',
            self::Revisi => 'revisi',
            self::Revised => 'revised',
            self::Approved => 'approved',
            self::Completed => 'completed',
        };
    }


}
<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Pending = 'pending'; // File baru diunggah, menunggu review.
    case Onprogress = 'onprogress'; // User meminta revisi.
    case Completed = 'completed'; // Proses selesai.

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Onprogress => 'On Progress',
            self::Completed => 'Completed',
        };
    }
}

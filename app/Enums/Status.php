<?php

namespace App\Enums;

enum Status: string
{
    case Uploaded = 'upload';
    case Revisi = 'revisi';
    case Approve = 'approve';

    public function label(): string
    {
        return match ($this) {
            self::Uploaded => 'upload',
            self::Revisi => 'revisi',
            self::Approve => 'approve'
        };
    }


}
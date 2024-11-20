<?php

namespace App\Enums;

enum Status: string
{
    case Uploaded = 'Uploaded';
    case Revisi = 'Revisi';
    case Approve = 'Approve';

    public function label(): string
    {
        return match ($this) {
            self::Uploaded => 'Uploaded',
            self::Revisi => 'Revisi',
            self::Approve => 'Approve'
        };
    }


}
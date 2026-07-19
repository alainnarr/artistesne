<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case ARTIST = 'artist';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrateur',
            self::ARTIST => 'Artiste',
        };
    }
}

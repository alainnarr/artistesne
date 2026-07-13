<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    case OPEN = 'open';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case PENDING = 'pending';

    public function label()
    {
        return match ($this) {
            self::OPEN => 'Ouvert',
            self::APPROVED => 'Approuvé',
            self::REJECTED => 'Rejeté',
            self::PENDING => 'En attente',
        };
    }
}

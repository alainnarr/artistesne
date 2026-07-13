<?php

namespace App\Enums;

enum DisciplineType: string
{
    case MAIN = 'main';
    case SECONDARY = 'secondary';

    public function label()
    {
        return match ($this) {
            self::MAIN => 'principal',
            self::SECONDARY => 'secondaire',
        };
    }
}

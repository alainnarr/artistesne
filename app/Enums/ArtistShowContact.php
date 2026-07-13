<?php

namespace App\Enums;

enum ArtistShowContact: int
{
    case HIDE = 0;
    case SHOW = 1;

    public function label(): string
    {
        return match ($this) {
            self::SHOW => 'Show',
            self::HIDE => 'Hide',
        };
    }

    public function toBool(): bool
    {
        return $this === self::SHOW;
    }

    public static function fromBool(bool $value): self
    {
        return $value ? self::SHOW : self::HIDE;
    }
}

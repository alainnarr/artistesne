<?php

namespace App\Enums;

use App\Enums\Concerns\ProvidesColor;
use App\Enums\Concerns\ProvidesLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ArtistStatus: string implements HasColor, HasLabel
{
    use ProvidesColor, ProvidesLabel;

    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Brouillon',
            self::Published => 'Publié',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Published => 'success',
        };
    }
}

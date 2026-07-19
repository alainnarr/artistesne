<?php

namespace App\Enums;

use App\Enums\Concerns\ProvidesColor;
use App\Enums\Concerns\ProvidesLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ArtistStatus: string implements HasColor, HasLabel
{
    use ProvidesColor, ProvidesLabel;

    case DRAFT = 'draft';
    case PUBLISHED = 'published';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::PUBLISHED => 'Publié',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::PUBLISHED => 'success',
        };
    }
}

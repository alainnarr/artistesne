<?php

namespace App\Enums;

use App\Enums\Concerns\ProvidesColor;
use App\Enums\Concerns\ProvidesLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ArtistChangeRequestStatus: string implements HasColor, HasLabel
{
    use ProvidesColor, ProvidesLabel;

    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CHANGES_REQUESTED = 'changes_requested';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::APPROVED => 'Approuvée',
            self::REJECTED => 'Refusée',
            self::CHANGES_REQUESTED => 'Modifications demandées',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::CHANGES_REQUESTED => 'info',
        };
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }
}

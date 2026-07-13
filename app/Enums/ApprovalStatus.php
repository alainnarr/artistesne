<?php

namespace App\Enums;

use App\Enums\Concerns\ProvidesColor;
use App\Enums\Concerns\ProvidesLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ApprovalStatus: string implements HasColor, HasLabel
{
    use ProvidesColor, ProvidesLabel;

    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case ChangesRequested = 'changes_requested';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::Approved => 'Approuvée',
            self::Rejected => 'Refusée',
            self::ChangesRequested => 'Modifications demandées',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::ChangesRequested => 'info',
        };
    }

    public function isPending(): bool
    {
        return $this === self::Pending;
    }
}

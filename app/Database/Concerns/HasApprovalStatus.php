<?php

declare(strict_types=1);

namespace App\Database\Concerns;

use App\Database\Models\User;
use App\Enums\ArtistChangeRequestStatus;
use Illuminate\Database\Eloquent\Builder;

trait HasApprovalStatus
{
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ArtistChangeRequestStatus::PENDING);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', ArtistChangeRequestStatus::APPROVED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', ArtistChangeRequestStatus::REJECTED);
    }

    public function approve(User $reviewer, ?string $notes = null): bool
    {
        return $this->markReviewed(ArtistChangeRequestStatus::APPROVED, $reviewer, $notes);
    }

    public function reject(User $reviewer, ?string $notes = null): bool
    {
        return $this->markReviewed(ArtistChangeRequestStatus::REJECTED, $reviewer, $notes);
    }

    public function requestChanges(User $reviewer, string $notes): bool
    {
        return $this->markReviewed(ArtistChangeRequestStatus::CHANGES_REQUESTED, $reviewer, $notes);
    }

    public function isPending(): bool
    {
        return $this->status === ArtistChangeRequestStatus::PENDING;
    }

    protected function markReviewed(ArtistChangeRequestStatus $status, User $reviewer, ?string $notes): bool
    {
        $this->status = $status;
        $this->reviewed_by = $reviewer->getKey();
        $this->reviewed_at = now();
        $this->review_notes = $notes;

        return $this->save();
    }
}

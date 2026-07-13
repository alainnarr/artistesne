<?php

namespace App\Models\Concerns;

use App\Enums\ApprovalStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait HasApprovalStatus
{
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ApprovalStatus::Pending);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', ApprovalStatus::Approved);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', ApprovalStatus::Rejected);
    }

    public function approve(User $reviewer, ?string $notes = null): bool
    {
        return $this->markReviewed(ApprovalStatus::Approved, $reviewer, $notes);
    }

    public function reject(User $reviewer, ?string $notes = null): bool
    {
        return $this->markReviewed(ApprovalStatus::Rejected, $reviewer, $notes);
    }

    public function requestChanges(User $reviewer, string $notes): bool
    {
        return $this->markReviewed(ApprovalStatus::ChangesRequested, $reviewer, $notes);
    }

    protected function markReviewed(ApprovalStatus $status, User $reviewer, ?string $notes): bool
    {
        $this->status = $status;
        $this->reviewed_by = $reviewer->getKey();
        $this->reviewed_at = now();
        $this->review_notes = $notes;

        return $this->save();
    }
}

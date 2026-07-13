<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use App\Models\Concerns\HasApprovalStatus;
use Database\Factories\ArtistChangeRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistChangeRequest extends Model
{
    /** @use HasFactory<ArtistChangeRequestFactory> */
    use HasApprovalStatus, HasFactory;

    protected $fillable = [
        'artist_id',
        'submitted_by',
        'payload',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApprovalStatus::class,
            'payload' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Apply the proposed payload to the related artist.
     */
    public function apply(): void
    {
        $this->artist->forceFill($this->payload)->save();
    }
}

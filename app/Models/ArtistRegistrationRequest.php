<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use App\Models\Concerns\HasApprovalStatus;
use Database\Factories\ArtistRegistrationRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistRegistrationRequest extends Model
{
    /** @use HasFactory<ArtistRegistrationRequestFactory> */
    use HasApprovalStatus, HasFactory;

    protected $fillable = [
        // Identité
        'full_name',
        'artist_name',
        'show_artist_name',
        'birth_date',
        // Contact
        'email',
        'phone',
        'display_contact_button',
        // Territorialité
        'residence_location',
        'locality',
        'commune',
        'canton_link',
        // Domaine
        'main_domain',
        'main_activity',
        'main_activity_other',
        'main_activities',
        // Critères
        'training',
        'paid_activity',
        'recognition',
        'recent_achievement',
        'last_activity',
        // Documents
        'documents_info',
        'documents',
        'links',
        // Workflow
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApprovalStatus::class,
            'reviewed_at' => 'datetime',
            'birth_date' => 'date',
            'show_artist_name' => 'boolean',
            'display_contact_button' => 'boolean',
            'links' => 'array',
            'documents' => 'array',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}

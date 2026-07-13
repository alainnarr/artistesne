<?php

namespace App\Models;

use App\Enums\ArtistStatus;
use Database\Factories\ArtistFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Database\Models\Repository;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Artist extends Model
{
    /** @use HasFactory<ArtistFactory> */
    use HasFactory, Searchable;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'discipline',
        'secondary_discipline',
        'biography',
        'email',
        'phone',
        'cover_image',
        'links',
        'status',
        'published_at',
        'city',
        'activities',
        'secondary_activities',
        'keywords',
        'collaborations',
        'display_contact_button',
        'last_confirmed_at',
        'reminder_sent_at',
        'confirmation_token',
    ];

    protected function casts(): array
    {
        return [
            'status' => ArtistStatus::class,
            'display_contact_button' => 'boolean',
            'links' => 'array',
            'activities' => 'array',
            'secondary_activities' => 'array',
            'keywords' => 'array',
            'collaborations' => 'array',
            'published_at' => 'datetime',
            'last_confirmed_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    // ── Scout ────────────────────────────────────────────────────────────────

    public function searchableAs(): string
    {
        return 'artists';
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'discipline' => $this->discipline,
            'secondary_discipline' => $this->secondary_discipline,
            'city' => $this->city,
            'activities' => implode(' ', $this->activities ?? []),
            'secondary_activities' => implode(' ', $this->secondary_activities ?? []),
            'keywords' => implode(' ', $this->keywords ?? []),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->isPublished();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function changeRequests(): HasMany
    {
        return $this->hasMany(ArtistChangeRequest::class);
    }

    public function pendingChangeRequest(): ?ArtistChangeRequest
    {
        return $this->changeRequests()->pending()->latest()->first();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ArtistStatus::Published);
    }

    public function isPublished(): bool
    {
        return $this->status === ArtistStatus::Published;
    }


    public function repositories(): MorphMany
    {
        return $this->morphMany(Repository::class, 'repositoryable');
    }
}

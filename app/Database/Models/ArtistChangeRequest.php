<?php

namespace App\Database\Models;

use App\Database\Model;
use App\Database\Traits\PreventUpdate;
use App\Database\Traits\PreventDelete;
use App\Enums\ArtistChangeRequestStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Validation\Rules\Enum;

class ArtistChangeRequest extends Model
{
    use PreventUpdate;
    use PreventDelete;

    protected $table = 'artists_change_requests';

    protected $fillable = [
        'artist_id',
        'payload',
        'enum_status',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
    ];

    protected $updatable = [
        'enum_status',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'enum_status' => ArtistChangeRequestStatus::class,
        ];
    }

    /* * * * * * * * VALIDATION * * * * * * * */
    public static function getRules(array $fields = [], $register = null): array
    {
        $rules = [
            'artist_id' => 'required|integer|exists:artists,id',
            'payload' => 'required|json',
            'enum_status' => ['required', new Enum(ArtistChangeRequestStatus::class)],
            'reviewed_at' => 'nullable|date',
            'reviewed_by' => 'nullable|integer|exists:users,id',
            'review_notes' => 'nullable|string',
        ];

        if (empty($fields)) {
            return $rules;
        }

        return array_intersect_key($rules, array_flip($fields));
    }
    /* * * * * * * * END - VALIDATION * * * * * * * */

    /* * * * * * * * RELATIONS * * * * * * * */
    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class, 'artist_id');
    }

    public function repositories(): MorphMany
    {
        return $this->morphMany(Repository::class, 'repositoryable');
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Repository::class, 'repositoryable');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
    /* * * * * * * * END - RELATIONS * * * * * * * */
}

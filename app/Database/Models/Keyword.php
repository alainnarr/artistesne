<?php

declare(strict_types=1);

namespace App\Database\Models;

use App\Database\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Keyword extends Model
{
    protected $table = 'keywords';

    protected $fillable = [
        'label',
    ];

    protected $updatable = [];

    /* * * * * * * * VALIDATION * * * * * * * */
    /** @return array<string, string|array> */
    public static function getRules(array $fields = [], $register = null): array
    {
        $rules = [
            'label' => 'required|string|max:125',
        ];

        if (empty($fields)) {
            return $rules;
        }

        return array_intersect_key($rules, array_flip($fields));
    }
    /* * * * * * * * END - VALIDATION * * * * * * * */

    /* * * * * * * * RELATIONS * * * * * * * */
    /** @return BelongsToMany<Artist, $this> */
    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'keywords_artists', 'keyword_id', 'artist_id');
    }

    /** @return HasMany<KeywordArtist, $this> */
    public function keywordsArtists(): HasMany
    {
        return $this->hasMany(KeywordArtist::class, 'keyword_id');
    }
    /* * * * * * * * END - RELATIONS * * * * * * * */
}

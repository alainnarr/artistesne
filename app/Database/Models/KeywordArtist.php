<?php

namespace App\Database\Models;

use App\Database\Model;
use App\Database\Traits\PreventUpdate;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeywordArtist extends Model
{
    use PreventUpdate;

    protected $table = 'keywords_artists';

    protected $fillable = [
        'keyword_id',
        'artist_id'
    ];

    /* * * * * * * * VALIDATION * * * * * * * */
    public static function getRules(array $fields = [], $register = null): array
    {
        $rules = [
            'keyword_id' => 'required|integer|exists:keywords,id',
            'artist_id' => 'required|integer|exists:artists,id',
        ];

        if (empty($fields)) {
            return $rules;
        }

        return array_intersect_key($rules, array_flip($fields));
    }
    /* * * * * * * * END - VALIDATION * * * * * * * */

    /* * * * * * * * RELATIONS * * * * * * * */
    public function keyword(): BelongsTo
    {
        return $this->belongsTo(Keyword::class, 'keyword_id');
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class, 'artist_id');
    }
    /* * * * * * * * END - RELATIONS * * * * * * * */
}

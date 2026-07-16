<?php

declare(strict_types=1);

namespace App\Database\Models;

use App\Database\Model;
use App\Database\Traits\PreventUpdate;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\LinkType;
use Illuminate\Validation\Rules\Enum;

class Link extends Model
{
    use PreventUpdate;

    protected $table = 'links';

    protected $fillable = [
        'artist_id',
        'registration_id',
        'link',
        'enum_type',
    ];

    protected $updatable = [
        'link',
    ];

    /** @return array<string, class-string|'datetime'> */
    protected function casts(): array
    {
        return [
            'enum_type' => LinkType::class,
        ];
    }

    /* * * * * * * * VALIDATION * * * * * * * */
    /** @return array<string, string|array> */
    public static function getRules(array $fields = [], $register = null): array
    {
        $rules = [
            'artist_id' => [
                'required_without:registration_id',
                'prohibited_with:registration_id',
                'nullable',
                'integer',
                'exists:artists,id',
            ],
            'registration_id' => [
                'required_without:artist_id',
                'prohibited_with:artist_id',
                'nullable',
                'integer',
                'exists:registrations,id',
            ],
            'link' => 'required|string|url:http,https|max:255',
            'enum_type' => ['required', new Enum(LinkType::class)],
        ];

        if (empty($fields)) {
            return $rules;
        }

        return array_intersect_key($rules, array_flip($fields));
    }
    /* * * * * * * * END - VALIDATION * * * * * * * */

    /* * * * * * * * RELATIONS * * * * * * * */
    /** @return BelongsTo<Artist, $this> */
    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class, 'artist_id');
    }

    /** @return BelongsTo<Registration, $this> */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }
    /* * * * * * * * END - RELATIONS * * * * * * * */
}

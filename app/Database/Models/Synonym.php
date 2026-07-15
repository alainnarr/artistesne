<?php

declare(strict_types=1);

namespace App\Database\Models;

use App\Database\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Synonym extends Model
{
    protected $table = 'synonyms';

    protected $fillable = [
        'activity_id',
        'label',
    ];

    /* * * * * * * * VALIDATION * * * * * * * */
    public static function getRules(array $fields = [], $register = null): array
    {
        $rules = [
            'activity_id' => 'required|integer|exists:activities,id',
            'label' => 'required|string|max:125',
        ];

        if (empty($fields)) {
            return $rules;
        }

        return array_intersect_key($rules, array_flip($fields));
    }
    /* * * * * * * * END - VALIDATION * * * * * * * */

    /* * * * * * * * RELATIONS * * * * * * * */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }
    /* * * * * * * * END - RELATIONS * * * * * * * */
}

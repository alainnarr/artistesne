<?php

declare(strict_types=1);

namespace App\Database\Models;

use App\Database\Model;
use App\Database\Traits\PreventUpdate;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityRegistration extends Model
{
    use PreventUpdate;

    protected $table = 'activities_registrations';

    protected $fillable = [
        'activity_id',
        'registration_id',
    ];

    /* * * * * * * * VALIDATION * * * * * * * */
    /** @return array<string, string|array> */
    public static function getRules(array $fields = [], $register = null): array
    {
        $rules = [
            'activity_id' => 'required|integer|exists:activities,id',
            'registration_id' => 'required|integer|exists:registrations,id',
        ];

        if (empty($fields)) {
            return $rules;
        }

        return array_intersect_key($rules, array_flip($fields));
    }
    /* * * * * * * * END - VALIDATION * * * * * * * */

    /* * * * * * * * RELATIONS * * * * * * * */
    /** @return BelongsTo<Activity, $this> */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    /** @return BelongsTo<Registration, $this> */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }
    /* * * * * * * * END - RELATIONS * * * * * * * */
}

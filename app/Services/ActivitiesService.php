<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Models\ActivityRegistration;
use App\Database\Models\Registration;

class ActivitiesService
{
    /**
     * Attach multiple activities to a registration by creating ActivityRegistration pivot rows.
     *
     * We do NOT use Eloquent's attach() because it bypasses model events and
     * therefore the Auditable trait would not record the inserts.
     *
     * @param  array<int, int>  $activityIds
     */
    public function attachMultiple(Registration $registration, array $activityIds): void
    {
        foreach ($activityIds as $activityId) {
            ActivityRegistration::create([
                'registration_id' => $registration->id,
                'activity_id' => (int) $activityId,
            ]);
        }
    }

    /**
     * Sync activities on a registration: remove all existing pivot rows and
     * re-attach the provided list.
     *
     * ActivityRegistration uses PreventUpdate but not PreventDelete, so direct
     * deletion is allowed. Each deletion fires the Auditable deleting hook,
     * writing a HARDDELETE record to the generic audits table.
     *
     * @param  array<int, int>  $activityIds
     */
    public function sync(Registration $registration, array $activityIds): void
    {
        ActivityRegistration::where('registration_id', $registration->id)
            ->get()
            ->each(fn (ActivityRegistration $row) => $row->delete());

        $this->attachMultiple($registration, $activityIds);
    }
}

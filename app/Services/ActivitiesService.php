<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\Models\Artist;
use App\Database\Models\Registration;
use App\Database\Models\ActivityArtist;
use App\Database\Models\ActivityRegistration;

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
    public function attach(Registration|Artist $owner, int $activityId): ActivityRegistration|ActivityArtist
    {
        return match (true) {
            $owner instanceof Registration => ActivityRegistration::firstOrCreate([
                'activity_id' => $activityId,
                'registration_id' => $owner->id,
            ]),

            $owner instanceof Artist => ActivityArtist::firstOrCreate([
                'activity_id' => $activityId,
                'artist_id' => $owner->id,
            ]),
        };
    }

    public function detach(Registration|Artist $owner, int $activityId): bool
    {
        return match (true) {
            $owner instanceof Registration => (bool) ActivityRegistration::where([
                'activity_id' => $activityId,
                'registration_id' => $owner->id,
            ])->delete(),

            $owner instanceof Artist => (bool) ActivityArtist::where([
                'activity_id' => $activityId,
                'artist_id' => $owner->id,
            ])->delete(),
        };
    }

    public function attachMultiple(Registration|Artist $owner, array $activityIds): array
    {
        $records = [];

        foreach ($activityIds as $activityId) {
            if (!is_numeric($activityId)) {
                continue;
            }
            $records[] = $this->attach($owner, intval($activityId));
        }

        return $records;
    }

    public function detachMultiple(Registration|Artist $owner, array $activityIds): int
    {
        $deleted = 0;

        foreach ($activityIds as $activityId) {
            $deleted += (int) $this->detach($owner, $activityId);
        }

        return $deleted;
    }

    /**
     * Sync activities on a registration
     *
     * @param  array<int, int>  $activityIds
     */
    public function sync(Registration|Artist $owner, array $activityIds): void
    {
        $currentActivityIds = match (true) {
            $owner instanceof Registration => ActivityRegistration::where('registration_id', $owner->id)
                ->pluck('activity_id')
                ->toArray(),

            $owner instanceof Artist => ActivityArtist::where('artist_id',$owner->id)
                ->pluck('activity_id')
                ->toArray(),
        };

        $attach = array_diff($activityIds, $currentActivityIds);
        $detach = array_diff($currentActivityIds, $activityIds);

        if (!empty($attach)) {
            $this->attachMultiple($owner, $attach);
        }

        if (!empty($detach)) {
            $this->detachMultiple($owner, $detach);
        }
    }
}

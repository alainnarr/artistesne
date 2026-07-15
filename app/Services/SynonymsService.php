<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Models\Activity;
use App\Database\Models\Synonym;
use Illuminate\Support\Facades\Validator;

class SynonymsService
{
    public function create(Activity $activity, string $label): Synonym
    {
        Validator::make(
            ['activity_id' => $activity->id, 'label' => $label],
            Synonym::getRules(),
        )->validate();

        return $activity->synonyms()->create(['label' => $label]);
    }

    public function update(Activity $activity, string $label, string $newLabel): Synonym
    {
        $synonym = $activity->synonyms()->where('label', $label)->firstOrFail();

        Validator::make(
            ['activity_id' => $activity->id, 'label' => $newLabel],
            Synonym::getRules(),
        )->validate();

        $synonym->update(['label' => $newLabel]);

        return $synonym;
    }

    public function delete(Activity $activity, string $label): void
    {
        $activity->synonyms()->where('label', $label)->firstOrFail()->delete();
    }
}

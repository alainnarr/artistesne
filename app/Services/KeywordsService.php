<?php

namespace App\Services;

use App\Database\Models\Keyword;
use App\Database\Models\KeywordArtist;
use App\Database\Models\Artist;

class KeywordsService
{
    /**
     * Method for managing Many-to-Many relationships that can be tracked by the audit system.
     */
    public function attach(Artist $artist, string $label): Keyword
    {
        $keyword = Keyword::firstOrCreate(['label' => strtolower($label)]);

        KeywordArtist::firstOrCreate([
            'artist_id' => $artist->id,
            'keyword_id' => $keyword->id,
        ]);

        return $keyword;
    }

    public function detach(Artist $artist, string $label): bool
    {
        $keyword = Keyword::where('label', strtolower($label))->first();

        if (!$keyword) {
            return false;
        }

        return (bool) KeywordArtist::where([
            'artist_id' => $artist->id,
            'keyword_id' => $keyword->id,
        ])->delete();
    }

    public function attachMultiple(Artist $artist, array $labels): array
    {
        $records = [];

        foreach ($labels as $label) {
            $records[] = $this->attach($artist, $label);
        }

        return $records;
    }

    public function detachMultiple(Artist $artist, array $labels): int
    {
        $deleted = 0;

        foreach ($labels as $label) {
            $deleted += (int) $this->detach($artist, $label);
        }

        return $deleted;
    }

    public function sync(Artist $artist, array $labels): void
    {
        $labels = array_values(array_unique(array_map('strtolower', $labels)));

        $currentLabels = $artist->keywords()->pluck('label')->toArray();

        $attach = array_diff($labels, $currentLabels);
        $detach = array_diff($currentLabels, $labels);

        if (!empty($attach)) {
            $this->attachMultiple($artist, $attach);
        }

        if (!empty($detach)) {
            $this->detachMultiple($artist, $detach);
        }
    }

    public function delete(Keyword $keyword): bool
    {
        // Detach all artists associated with the keyword
        KeywordArtist::where('keyword_id', $keyword->id)->delete();

        // Delete the keyword itself
        return (bool) $keyword->delete();
    }
}

<?php

namespace Database\Seeders;

use App\Models\Artist;
use App\Models\TaxonomyTerm;
use Illuminate\Database\Seeder;

class KeywordTaxonomySeeder extends Seeder
{
    public function run(): void
    {
        // Extract all unique keywords from published artists and insert them
        // as managed taxonomy terms so they appear in search suggestions.
        $existing = TaxonomyTerm::query()
            ->where('type', 'keywords')
            ->pluck('name')
            ->map(fn ($n) => mb_strtolower($n))
            ->all();

        $keywords = Artist::query()
            ->published()
            ->whereNotNull('keywords')
            ->pluck('keywords')
            ->flatten()
            ->map(fn ($k) => trim((string) $k))
            ->filter()
            ->unique(fn ($k) => mb_strtolower($k))
            ->reject(fn ($k) => in_array(mb_strtolower($k), $existing, true))
            ->sort()
            ->values();

        $position = TaxonomyTerm::query()->where('type', 'keywords')->max('position') ?? 0;

        foreach ($keywords as $keyword) {
            TaxonomyTerm::create([
                'domain' => null,
                'type' => 'keywords',
                'name' => $keyword,
                'position' => ++$position,
            ]);
        }

        $this->command->info("Inserted {$keywords->count()} keyword taxonomy terms.");
    }
}

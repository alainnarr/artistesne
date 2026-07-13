<?php

namespace Database\Seeders;

use App\Models\TaxonomyTerm;
use Illuminate\Database\Seeder;

class TaxonomyTermsSeeder extends Seeder
{
    /**
     * Canonical artistic domains, seeded once as `taxonomy_terms` rows so
     * they're administered from the Filament "Taxonomies" page instead of a
     * hardcoded PHP enum. Keyed by the stable slug used to scope
     * `main_activities` to a domain (see `config/taxonomy.php`); the value is
     * the display label persisted on `Artist::$discipline`.
     *
     * @var array<string, string>
     */
    private const DOMAINS = [
        'musique' => 'Musique',
        'spectacle_vivant' => 'Spectacle vivant',
        'arts_visuels' => 'Arts visuels',
        'cinema_audiovisuel' => 'Cinéma et audiovisuel',
        'litterature_ecriture' => 'Littérature et écriture',
        'arts_numeriques' => 'Arts numériques',
    ];

    /**
     * Seed the taxonomy_terms table from the canonical config/taxonomy.php data.
     *
     * Running this seeder multiple times is safe: it uses updateOrCreate so
     * existing terms are not duplicated, and position values are preserved.
     */
    public function run(): void
    {
        $this->seedDomains();
        $this->seedMainActivities();
    }

    public function seedDomains(): void
    {
        foreach (array_keys(self::DOMAINS) as $position => $slug) {
            TaxonomyTerm::updateOrCreate(
                ['type' => 'domain', 'slug' => $slug],
                ['name' => self::DOMAINS[$slug], 'domain' => null, 'position' => $position],
            );
        }

        $count = TaxonomyTerm::where('type', 'domain')->count();
        $this->command?->info("TaxonomyTerms seeded: {$count} domain term(s).");
    }

    private function seedMainActivities(): void
    {
        /** @var array<string, list<string>> $mainActivities */
        $mainActivities = config('taxonomy.main_activities', []);

        foreach ($mainActivities as $domainValue => $names) {
            foreach ($names as $position => $name) {
                TaxonomyTerm::updateOrCreate(
                    [
                        'domain' => $domainValue,
                        'type' => 'main_activities',
                        'name' => $name,
                    ],
                    ['position' => $position],
                );
            }
        }

        $count = TaxonomyTerm::where('type', 'main_activities')->count();
        $this->command?->info("TaxonomyTerms seeded: {$count} main activity term(s) across ".count($mainActivities).' domains.');
    }
}

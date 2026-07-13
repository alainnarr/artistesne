<?php

use App\Models\Artist;
use App\Models\SearchSynonym;
use App\Models\TaxonomyTerm;
use Database\Seeders\StagingSeeder;
use Illuminate\Support\Facades\Artisan;

test('staging seeder seeds taxonomy and demo data', function () {
    Artisan::call('db:seed', ['--class' => StagingSeeder::class]);

    expect(Artist::query()->count())->toBeGreaterThan(0)
        ->and(TaxonomyTerm::query()->where('type', 'main_activities')->count())->toBeGreaterThan(0)
        ->and(TaxonomyTerm::query()->where('type', 'keywords')->count())->toBeGreaterThan(0)
        ->and(SearchSynonym::query()->count())->toBeGreaterThan(0);
});

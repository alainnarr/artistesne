<?php

use App\Database\Models\Artist;
use App\Database\Models\SearchSynonym;
use Database\Seeders\StagingSeeder;
use Illuminate\Support\Facades\Artisan;

test('staging seeder seeds demo data', function () {
    Artisan::call('db:seed', ['--class' => StagingSeeder::class]);

    expect(Artist::query()->count())->toBeGreaterThan(0)
        ->and(SearchSynonym::query()->count())->toBeGreaterThan(0);
});

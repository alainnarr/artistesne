<?php

use Database\Seeders\ActivitiesSeeder;
use Database\Seeders\DisciplinesSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->beforeEach(function (): void {
        // Fake Livewire's temp-upload disk so tests using TemporaryUploadedFile
        // don't fail with a "Permission denied" on the real temp directory.
        Storage::fake('tmp-for-tests');

        // New data model: disciplines + activities used by RegisterArtist
        // form validation (Rule::exists('disciplines', 'id'), etc.).
        (new DisciplinesSeeder)->run();
        (new ActivitiesSeeder)->run();
    })
    ->in('Feature');

// Dev gallery smoke tests live under tests/Unit/Dev — they boot the framework via
// TestCase but skip RefreshDatabase since they are purely presentational.
pest()->extend(TestCase::class)->in('Unit/Dev');

// Browser tests use Pest 4 + Laravel Pao (Playwright). They need the framework
// booted but not RefreshDatabase by default.
pest()->extend(TestCase::class)->in('Browser');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| Helpers globaux disponibles dans tous les fichiers de test.
|
*/

use App\Database\Models\Activity;
use App\Database\Models\Discipline;
use Illuminate\Support\Facades\Storage;

/** Resolves a seeded discipline ID (as string for wire:model compatibility). */
function disciplineId(string $code = 'musique'): string
{
    return (string) Discipline::where('code', $code)->value('id');
}

/** Resolves a seeded activity ID (as string for wire:model compatibility). */
function activityId(string $code = 'musique.chanteur'): string
{
    return (string) Activity::where('code', $code)->value('id');
}

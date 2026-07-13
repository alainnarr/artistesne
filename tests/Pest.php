<?php

use Database\Seeders\TaxonomyTermsSeeder;
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
        // Artistic domains are administered via TaxonomyTerm (type "domain")
        // instead of a hardcoded enum — most registration-flow Feature tests
        // rely on the canonical slugs (e.g. "musique") being valid, so seed
        // them automatically rather than requiring every test to do it.
        (new TaxonomyTermsSeeder)->seedDomains();
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

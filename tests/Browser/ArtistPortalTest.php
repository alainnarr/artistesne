<?php

declare(strict_types=1);

use App\Database\Models\User;
use Database\Seeders\ActivitiesSeeder;
use Database\Seeders\DisciplinesSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    (new DisciplinesSeeder)->run();
    (new ActivitiesSeeder)->run();
});

/*
|--------------------------------------------------------------------------
| Portail artiste & panneau admin — parcours authentifiés (Playwright)
|--------------------------------------------------------------------------
|
| L'authentification est faite par programme via actingAs() (les artistes se
| connectent par magic link et les admins via AD FS : pas de mot de passe).
| actingAs() se propage bien à la session du navigateur.
|
*/

it('lets anyone (including authenticated artists) view the Espace Artistes hub in the browser', function () {
    visit(route('artist.login'))
        ->assertNoJavaScriptErrors()
        ->assertSee('Espace')
        ->assertSee('Référencement');
});

it('lets an admin open the Filament admin panel in the browser', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    visit('/admin')
        ->assertNoJavaScriptErrors();
});

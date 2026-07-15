<?php

declare(strict_types=1);

use App\Database\Models\Artist;
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

it('lets an authenticated artist view their dashboard in the browser', function () {
    $user = User::factory()->artist()->create();
    Artist::factory()->create(['user_id' => $user->id, 'artist_name' => 'Studio Test']);

    $this->actingAs($user);

    visit(route('artist.dashboard'))
        ->assertNoJavaScriptErrors()
        ->assertSee('Espace')
        ->assertSee('Référencement');
});

it('redirects an anonymous visitor away from the artist dashboard', function () {
    visit(route('artist.dashboard'))
        ->assertPathIs(parse_url(route('artist.login'), PHP_URL_PATH))
        ->assertNoJavaScriptErrors();
});

it('lets an admin open the Filament admin panel in the browser', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    visit('/admin')
        ->assertNoJavaScriptErrors();
});

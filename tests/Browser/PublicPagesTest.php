<?php

declare(strict_types=1);

use App\Models\Artist;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Parcours visiteur — pages publiques (Playwright)
|--------------------------------------------------------------------------
|
| Vérifie le rendu réel dans un navigateur (Alpine/JS inclus) et l'absence
| d'erreurs JavaScript sur les pages accessibles à un visiteur anonyme.
|
*/

it('renders the home page without JavaScript errors', function () {
    visit('/')
        ->assertNoJavaScriptErrors()
        ->assertSee('Annuaire des artistes')
        ->assertSee('Découvrir les artistes');
});

it('renders the public artists directory with published artists', function () {
    Artist::factory()->published()->create(['name' => 'Alice Martin']);
    Artist::factory()->published()->create(['name' => 'Bruno Dupont']);
    Artist::factory()->create(['name' => 'Brouillon Caché']); // draft, must not appear

    visit('/artistes')
        ->assertNoJavaScriptErrors()
        ->assertSee('Découvrir les artistes')
        ->assertSee('Alice Martin')
        ->assertSee('Bruno Dupont')
        ->assertDontSee('Brouillon Caché');
});

it('renders a published artist profile page', function () {
    $artist = Artist::factory()->published()->create(['name' => 'Alice Martin']);

    visit(route('public.artist.show', $artist))
        ->assertNoJavaScriptErrors()
        ->assertSee('Alice Martin');
});

it('renders the about page', function () {
    visit('/a-propos')
        ->assertNoJavaScriptErrors()
        ->assertSee('À propos');
});

it('renders the contact page', function () {
    visit('/contact')
        ->assertNoJavaScriptErrors()
        ->assertSee('Nous contacter');
});

it('renders the registration form landing (step 1)', function () {
    visit('/devenir-artiste')
        ->assertNoJavaScriptErrors()
        ->assertSee('référencement')
        ->assertSee('Étape suivante');
});

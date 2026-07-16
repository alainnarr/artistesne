<?php

declare(strict_types=1);

use App\Database\Models\Artist;
use App\Database\Models\User;

/*
|--------------------------------------------------------------------------
| Matrice de contrôle d'accès (visiteur / artiste / admin)
|--------------------------------------------------------------------------
|
| Vérifie les frontières entre les trois personas : portail artiste et
| panneau d'administration Filament.
|
*/

// --- Visiteur anonyme : le portail artiste est protégé -----------------------

it('redirects guests away from protected artist portal routes', function (string $routeName) {
    $this->get(route($routeName))->assertRedirect(route('artist.login'));
})->with([
    'profile edit' => 'artist.profile-edit',
]);

it('lets guests (and authenticated artists) reach the Espace Artistes hub', function () {
    $this->get(route('artist.login'))->assertOk();

    $user = User::factory()->artist()->create();
    $this->actingAs($user)->get(route('artist.login'))->assertOk();
});

// --- Admin : n'est pas un artiste, donc pas d'accès au portail artiste -------

it('redirects an admin away from the artist portal (not an artist)', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('artist.profile-edit'))
        ->assertRedirect(route('artist.login'));
});

// --- Artiste : accède à son portail ------------------------------------------

it('lets an authenticated artist reach the profile edit page', function () {
    $user = User::factory()->artist()->create();
    Artist::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('artist.profile-edit'))
        ->assertOk();
});

// --- Artiste : ne peut pas accéder au panneau d'administration ---------------

it('forbids an artist from accessing the Filament admin panel', function () {
    $user = User::factory()->artist()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

it('allows an admin to reach the Filament admin panel', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin')
        ->assertSuccessful();
});

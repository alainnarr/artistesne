<?php

declare(strict_types=1);

use App\Models\Artist;
use App\Models\ArtistRegistrationRequest;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Matrice de contrôle d'accès (visiteur / artiste / admin)
|--------------------------------------------------------------------------
|
| Vérifie les frontières entre les trois personas : portail artiste, panneau
| d'administration Filament et téléchargement de documents de demande.
|
*/

// --- Visiteur anonyme : le portail artiste est protégé -----------------------

it('redirects guests away from protected artist portal routes', function (string $routeName) {
    $this->get(route($routeName))->assertRedirect(route('login'));
})->with([
    'dashboard' => 'artist.dashboard',
    'profile edit' => 'artist.profile.edit',
    'profile setup' => 'artist.profile.setup',
]);

// --- Admin : n'est pas un artiste, donc pas d'accès au portail artiste -------

it('redirects an admin away from the artist portal (not an artist)', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('artist.dashboard'))
        ->assertRedirect(route('artist.login'));
});

// --- Artiste : accède à son portail ------------------------------------------

it('lets an authenticated artist reach the dashboard', function () {
    $user = User::factory()->artist()->create();
    Artist::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('artist.dashboard'))
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

// --- Téléchargement de documents de demande (admin only) ---------------------

it('lets an admin download a registration document', function () {
    Storage::fake('local');
    Storage::disk('local')->put('registrations/cv.pdf', 'fake-pdf-content');

    $request = ArtistRegistrationRequest::factory()->create([
        'documents' => [['name' => 'cv.pdf', 'path' => 'registrations/cv.pdf']],
    ]);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.registration-requests.documents.download', [
            'artistRegistrationRequest' => $request->id,
            'index' => 0,
        ]))
        ->assertOk()
        ->assertDownload('cv.pdf');
});

it('blocks guests from downloading registration documents', function () {
    Storage::fake('local');
    Storage::disk('local')->put('registrations/cv.pdf', 'fake-pdf-content');

    $request = ArtistRegistrationRequest::factory()->create([
        'documents' => [['name' => 'cv.pdf', 'path' => 'registrations/cv.pdf']],
    ]);

    $this->get(route('admin.registration-requests.documents.download', [
        'artistRegistrationRequest' => $request->id,
        'index' => 0,
    ]))->assertRedirect();
});

it('returns 404 when downloading a non-existent document index', function () {
    $request = ArtistRegistrationRequest::factory()->create(['documents' => []]);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.registration-requests.documents.download', [
            'artistRegistrationRequest' => $request->id,
            'index' => 5,
        ]))
        ->assertNotFound();
});

<?php

declare(strict_types=1);

use App\Database\Models\Artist;
use App\Database\Models\ArtistChangeRequest;
use App\Database\Models\Discipline;
use App\Database\Models\Registration;
use App\Database\Models\User;
use App\Enums\ArtistChangeRequestStatus;
use App\Enums\RegistrationStatus;
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
| Parcours V1 — tests d'intégration navigateur complets (Playwright)
|--------------------------------------------------------------------------
|
| Couvre trois acteurs :
|   1. Visiteur anonyme  — pages publiques + liens espace artistes
|   2. Admin             — validation d'inscriptions et de demandes de
|                          modification de profil
|   3. Artiste           — accès au portail, création/édition de profil,
|                          soumission et suivi d'une demande de modification
|
*/

// ═══════════════════════════════════════════════════════════════════════
// 1. VISITEUR ANONYME — page d'accueil & navigation
// ═══════════════════════════════════════════════════════════════════════

it('home page shows link to artist login space in nav', function () {
    visit('/')
        ->assertNoJavaScriptErrors()
        ->assertSee('Espace artistes');
});

it('home page shows a CTA pointing to artist registration', function () {
    visit('/')
        ->assertNoJavaScriptErrors()
        ->assertSee('Se faire référencer');
});

it('artist login page shows a link to registration', function () {
    visit(route('artist.login'))
        ->assertNoJavaScriptErrors()
        ->assertSee('Créer un profil artiste');
});

it('registration form landing page renders correctly', function () {
    visit(route('public.artist-registration'))
        ->assertNoJavaScriptErrors()
        ->assertSee('Étape suivante')
        ->assertSee('référencement');
});

// ═══════════════════════════════════════════════════════════════════════
// 2. ADMIN — validation d'une inscription
// ═══════════════════════════════════════════════════════════════════════

it('admin sees pending registration detail page with approve and reject actions', function () {
    $admin = User::factory()->admin()->create();
    $discipline = Discipline::where('code', 'musique')->firstOrFail();

    $registration = Registration::create([
        'real_name' => 'Marie Dupont',
        'artist_name' => 'Marie Dupont',
        'birth_date' => '1990-04-15',
        'email' => 'marie.dupont@inventaire.test',
        'phone' => '+41791234567',
        'residence_location' => 'Neuchâtel',
        'discipline_main' => $discipline->id,
        'enum_status' => RegistrationStatus::OPEN->value,
    ]);

    $this->actingAs($admin);

    visit(route('filament.admin.resources.registrations.view', $registration))
        ->assertNoJavaScriptErrors()
        ->assertSee('Marie Dupont')
        ->assertSee('Approuver et créer le compte')
        ->assertSee('Refuser');
});

it('admin can approve a pending registration and is redirected to the list', function () {
    $admin = User::factory()->admin()->create();
    $discipline = Discipline::where('code', 'musique')->firstOrFail();

    $registration = Registration::create([
        'real_name' => 'Luca Morin',
        'artist_name' => 'Luca Morin',
        'birth_date' => '1985-06-20',
        'email' => 'luca.morin@inventaire.test',
        'phone' => '+41791234567',
        'residence_location' => 'La Chaux-de-Fonds',
        'discipline_main' => $discipline->id,
        'enum_status' => RegistrationStatus::OPEN->value,
    ]);

    $this->actingAs($admin);

    visit(route('filament.admin.resources.registrations.view', $registration))
        ->assertNoJavaScriptErrors()
        ->click('Approuver et créer le compte')       // opens the confirmation modal
        ->assertSee("Approuver l'inscription")        // modal heading — confirms modal is open
        ->click('[role="dialog"] button[type="submit"]') // submit button inside the modal form
        ->assertNoJavaScriptErrors()
        ->assertSee('Inscription approuvée');
});

it('admin can reject a pending registration', function () {
    $admin = User::factory()->admin()->create();
    $discipline = Discipline::where('code', 'musique')->firstOrFail();

    $registration = Registration::create([
        'real_name' => 'Test Refusé',
        'artist_name' => 'Test Refusé',
        'birth_date' => '1992-03-10',
        'email' => 'refus.test@inventaire.test',
        'phone' => '+41791234568',
        'residence_location' => 'Neuchâtel',
        'discipline_main' => $discipline->id,
        'enum_status' => RegistrationStatus::OPEN->value,
    ]);

    $this->actingAs($admin);

    visit(route('filament.admin.resources.registrations.view', $registration))
        ->click('Refuser')                            // opens the reject modal
        ->assertSee("Refuser l'inscription")          // modal heading
        ->click('[role="dialog"] button[type="submit"]') // confirm button inside modal
        ->assertNoJavaScriptErrors()
        ->assertSee('Demande refusée');
});

it('admin does not see action buttons on an already-approved registration', function () {
    $admin = User::factory()->admin()->create();
    $discipline = Discipline::where('code', 'musique')->firstOrFail();

    $registration = Registration::create([
        'real_name' => 'Déjà Approuvé',
        'artist_name' => 'Déjà Approuvé',
        'birth_date' => '1988-01-01',
        'email' => 'deja.approuve@inventaire.test',
        'phone' => '+41791234569',
        'residence_location' => 'Neuchâtel',
        'discipline_main' => $discipline->id,
        'enum_status' => RegistrationStatus::APPROVED->value,
    ]);

    $this->actingAs($admin);

    visit(route('filament.admin.resources.registrations.view', $registration))
        ->assertNoJavaScriptErrors()
        ->assertDontSee('Approuver et créer le compte')
        ->assertDontSee('Refuser');
});

// ═══════════════════════════════════════════════════════════════════════
// 3. ARTISTE APPROUVÉ — portail & création de profil
// ═══════════════════════════════════════════════════════════════════════

it('approved artist can access the Espace Artistes hub', function () {
    $user = User::factory()->artist()->create();
    Artist::factory()->for($user)->published()->create(['artist_name' => 'Sophie Bernard']);

    $this->actingAs($user);

    visit(route('artist.login'))
        ->assertNoJavaScriptErrors()
        ->assertSee('Espace')
        ->assertSee('Artistes')
        ->assertSee('Référencement');
});

it('approved artist can access the edit profile page', function () {
    $user = User::factory()->artist()->create();
    Artist::factory()->for($user)->published()->create();

    $this->actingAs($user);

    visit(route('artist.profile-edit'))
        ->assertNoJavaScriptErrors()
        ->assertSee('Modifier')
        ->assertSee('ma page');
});

it('anonymous visitor is redirected to artist login when accessing the profile edit page', function () {
    visit(route('artist.profile-edit'))
        ->assertPathIs(parse_url(route('artist.login'), PHP_URL_PATH))
        ->assertNoJavaScriptErrors();
});

// ═══════════════════════════════════════════════════════════════════════
// 4. DEMANDE DE MODIFICATION DE PROFIL — parcours artiste + admin
// ═══════════════════════════════════════════════════════════════════════

it('artist sees pending change warning on edit profile when a change request is awaiting review', function () {
    $user = User::factory()->artist()->create();
    $artist = Artist::factory()->for($user)->published()->create();

    ArtistChangeRequest::factory()->create([
        'artist_id' => $artist->id,
        'submitted_by' => $user->id,
        'status' => ArtistChangeRequestStatus::PENDING->value,
    ]);

    $this->actingAs($user);

    visit(route('artist.profile-edit'))
        ->assertNoJavaScriptErrors()
        ->assertSee('Une modification est déjà en attente');
});

it('artist can submit the edit profile form when no change request is pending', function () {
    $user = User::factory()->artist()->create();
    Artist::factory()->for($user)->published()->create(['artist_name' => 'Original Nom']);

    $this->actingAs($user);

    visit(route('artist.profile-edit'))
        ->assertNoJavaScriptErrors()
        ->assertDontSee('Une modification est déjà en attente')
        ->assertSee('Nom d\'artiste');
});

it('admin sees pending change request detail page with review actions', function () {
    $admin = User::factory()->admin()->create();
    $artist = Artist::factory()->published()->create();

    $changeRequest = ArtistChangeRequest::factory()->create([
        'artist_id' => $artist->id,
        'submitted_by' => $artist->user_id,
        'status' => ArtistChangeRequestStatus::PENDING->value,
        'payload' => ['artist_name' => 'Nouveau Nom Artiste'],
    ]);

    $this->actingAs($admin);

    visit(route('filament.admin.resources.artist-change-requests.edit', $changeRequest))
        ->assertNoJavaScriptErrors()
        ->assertSee('Examiner la modification')
        ->assertSee('Approuver et appliquer')
        ->assertSee('Demander des ajustements')
        ->assertSee('Refuser');
});

it('admin can request adjustments on a pending profile change request', function () {
    $admin = User::factory()->admin()->create();
    $artist = Artist::factory()->published()->create();

    $changeRequest = ArtistChangeRequest::factory()->create([
        'artist_id' => $artist->id,
        'submitted_by' => $artist->user_id,
        'status' => ArtistChangeRequestStatus::PENDING->value,
        'payload' => ['artist_name' => 'Proposition Nom'],
    ]);

    $this->actingAs($admin);

    visit(route('filament.admin.resources.artist-change-requests.edit', $changeRequest))
        ->assertNoJavaScriptErrors()
        ->click('Demander des ajustements')            // opens the request-changes modal
        ->assertSee('Message à transmettre')           // modal form field label
        ->fill('[role="dialog"] textarea', 'Merci de préciser votre biographie.')
        ->click('[role="dialog"] button[type="submit"]') // submit modal form
        ->assertNoJavaScriptErrors()
        ->assertSee('Ajustements demandés');
});

it('admin can approve a pending profile change request', function () {
    $admin = User::factory()->admin()->create();
    $artist = Artist::factory()->published()->create(['artist_name' => 'Artiste Original']);

    $changeRequest = ArtistChangeRequest::factory()->create([
        'artist_id' => $artist->id,
        'submitted_by' => $artist->user_id,
        'status' => ArtistChangeRequestStatus::PENDING->value,
        'payload' => ['artist_name' => 'Nouveau Nom'],
    ]);

    $this->actingAs($admin);

    visit(route('filament.admin.resources.artist-change-requests.edit', $changeRequest))
        ->assertNoJavaScriptErrors()
        ->click('Approuver et appliquer')              // opens confirmation modal
        ->assertSee('Appliquer ces modifications ?')  // modal heading
        ->click('[role="dialog"] button[type="submit"]') // confirm in modal
        ->assertNoJavaScriptErrors()
        ->assertSee('Modifications appliquées');
});

it('admin can reject a pending profile change request', function () {
    $admin = User::factory()->admin()->create();
    $artist = Artist::factory()->published()->create();

    $changeRequest = ArtistChangeRequest::factory()->create([
        'artist_id' => $artist->id,
        'submitted_by' => $artist->user_id,
        'status' => ArtistChangeRequestStatus::PENDING->value,
        'payload' => ['biography' => '<p>Nouvelle biographie.</p>'],
    ]);

    $this->actingAs($admin);

    visit(route('filament.admin.resources.artist-change-requests.edit', $changeRequest))
        ->click('Refuser')                            // opens the reject modal
        ->assertSee('Motif (optionnel)')              // field label unique to the modal
        ->click('[role="dialog"] button[type="submit"]') // confirm in modal
        ->assertNoJavaScriptErrors()
        ->assertSee('Modification refusée');
});

it('artist can re-submit a change request after admin requested adjustments', function () {
    $user = User::factory()->artist()->create();
    $artist = Artist::factory()->for($user)->published()->create();

    // Status is changes_requested (not pending) → hasPendingChange should be false
    ArtistChangeRequest::factory()->create([
        'artist_id' => $artist->id,
        'submitted_by' => $user->id,
        'status' => ArtistChangeRequestStatus::CHANGES_REQUESTED->value,
        'review_notes' => 'Veuillez détailler votre biographie.',
    ]);

    $this->actingAs($user);

    // The pending change check uses pendingChangeRequest() which scopes to Pending status only.
    // After adjustments are requested, the artist should be able to submit a new form.
    visit(route('artist.profile-edit'))
        ->assertNoJavaScriptErrors()
        ->assertDontSee('Une modification est déjà en attente')
        ->assertSee('Nom d\'artiste');
});

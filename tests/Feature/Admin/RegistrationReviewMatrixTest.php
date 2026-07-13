<?php

declare(strict_types=1);

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use App\Filament\Resources\ArtistRegistrationRequests\Pages\EditArtistRegistrationRequest;
use App\Mail\AdminContactMail;
use App\Models\Artist;
use App\Models\ArtistRegistrationRequest;
use App\Models\User;
use App\Notifications\MagicLinkNotification;
use App\Notifications\RegistrationApprovedNotification;
use App\Notifications\RegistrationRejectedNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

/*
|--------------------------------------------------------------------------
| Matrice de validation des demandes de référencement (persona admin)
|--------------------------------------------------------------------------
|
| Complète tests/Feature/Admin/AdminWorkflowsTest.php avec la matrice
| rigoureuse des transitions de statut, des notifications, des garde-fous
| (actions masquées hors "pending") et des règles de validation.
|
*/

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

/**
 * @return Testable
 */
function editRegistration(ArtistRegistrationRequest $request)
{
    return Livewire::test(EditArtistRegistrationRequest::class, ['record' => $request->id]);
}

it('approval sends both the approved notification and the magic link, and stamps the throttle', function () {
    Notification::fake();

    $request = ArtistRegistrationRequest::factory()->create([
        'artist_name' => 'Camille Résonance',
        'email' => 'camille@inventaire.test',
    ]);

    editRegistration($request)->callAction('approve', ['notes' => null]);

    $user = User::where('email', 'camille@inventaire.test')->firstOrFail();

    expect($user->role)->toBe(UserRole::Artist)
        ->and($user->password)->toBeNull()
        ->and($user->last_magic_link_sent_at)->not->toBeNull();

    Notification::assertSentTo($user, RegistrationApprovedNotification::class);
    Notification::assertSentTo($user, MagicLinkNotification::class);
});

it('approval sets the created artist discipline from the request main domain', function () {
    $request = ArtistRegistrationRequest::factory()->create([
        'artist_name' => 'Domaine Test',
        'email' => 'domaine@inventaire.test',
        'main_domain' => 'Musique',
    ]);

    editRegistration($request)->callAction('approve', ['notes' => null]);

    $user = User::where('email', 'domaine@inventaire.test')->firstOrFail();

    expect($user->artist->discipline)->toBe('Musique');
});

it('approval reuses an existing user with the same email instead of duplicating it', function () {
    $existing = User::factory()->artist()->create(['email' => 'reuse@inventaire.test']);

    $request = ArtistRegistrationRequest::factory()->create([
        'artist_name' => 'Reuse Artist',
        'email' => 'reuse@inventaire.test',
    ]);

    editRegistration($request)->callAction('approve', ['notes' => null]);

    expect(User::where('email', 'reuse@inventaire.test')->count())->toBe(1)
        ->and(Artist::where('user_id', $existing->id)->count())->toBe(1);
});

it('approval generates a unique slug when the base slug already exists', function () {
    Artist::factory()->create(['slug' => 'slug-collision']);

    $request = ArtistRegistrationRequest::factory()->create([
        'artist_name' => 'Slug Collision',
        'email' => 'collision@inventaire.test',
    ]);

    editRegistration($request)->callAction('approve', ['notes' => null]);

    $user = User::where('email', 'collision@inventaire.test')->firstOrFail();

    expect($user->artist->slug)->toBe('slug-collision-2');
});

it('rejection stamps reviewer metadata and notifies the applicant', function () {
    Notification::fake();

    $request = ArtistRegistrationRequest::factory()->create([
        'artist_name' => 'Refus Test',
        'email' => 'refus@inventaire.test',
    ]);

    editRegistration($request)->callAction('reject', ['notes' => 'Hors périmètre cantonal.']);

    $request->refresh();

    expect($request->status)->toBe(ApprovalStatus::Rejected)
        ->and($request->reviewed_by)->toBe($this->admin->id)
        ->and($request->reviewed_at)->not->toBeNull()
        ->and($request->review_notes)->toBe('Hors périmètre cantonal.');

    Notification::assertSentTimes(RegistrationRejectedNotification::class, 1);
});

it('requesting changes moves the request to changes_requested and requires a message', function () {
    $request = ArtistRegistrationRequest::factory()->create();

    editRegistration($request)
        ->callAction('requestChanges', ['notes' => ''])
        ->assertHasActionErrors(['notes' => 'required']);

    expect($request->fresh()->status)->toBe(ApprovalStatus::Pending);

    editRegistration($request)
        ->callAction('requestChanges', ['notes' => 'Merci de compléter votre dossier.'])
        ->assertHasNoActionErrors();

    expect($request->fresh()->status)->toBe(ApprovalStatus::ChangesRequested)
        ->and($request->fresh()->review_notes)->toBe('Merci de compléter votre dossier.');
});

it('contacting the applicant sends an admin email without changing the status', function () {
    Mail::fake();

    $request = ArtistRegistrationRequest::factory()->create([
        'email' => 'contact@inventaire.test',
        'artist_name' => 'Contact Test',
    ]);

    editRegistration($request)->callAction('contactApplicant', [
        'subject' => 'Précision requise',
        'body' => 'Pourriez-vous préciser votre parcours ?',
    ]);

    Mail::assertSent(AdminContactMail::class, fn (AdminContactMail $mail) => $mail->hasTo('contact@inventaire.test'));

    expect($request->fresh()->status)->toBe(ApprovalStatus::Pending);
});

it('hides review actions once a request is no longer pending', function (ApprovalStatus $status) {
    $request = ArtistRegistrationRequest::factory()->create(['status' => $status]);

    editRegistration($request)
        ->assertActionDoesNotExist('approve')
        ->assertActionDoesNotExist('reject')
        ->assertActionDoesNotExist('requestChanges');
})->with([
    'approved' => ApprovalStatus::Approved,
    'rejected' => ApprovalStatus::Rejected,
    'changes requested' => ApprovalStatus::ChangesRequested,
]);

it('exposes review actions while a request is pending', function () {
    $request = ArtistRegistrationRequest::factory()->create(['status' => ApprovalStatus::Pending]);

    editRegistration($request)
        ->assertActionExists('approve')
        ->assertActionExists('reject')
        ->assertActionExists('requestChanges');
});

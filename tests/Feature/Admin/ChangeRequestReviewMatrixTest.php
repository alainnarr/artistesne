<?php

declare(strict_types=1);

use App\Database\Models\Artist;
use App\Database\Models\ArtistChangeRequest;
use App\Database\Models\User;
use App\Enums\ArtistChangeRequestStatus;
use App\Filament\Resources\ArtistChangeRequests\Pages\EditArtistChangeRequest;
use App\Notifications\ChangeRequestDecisionNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

/*
|--------------------------------------------------------------------------
| Matrice de validation des demandes de modification (persona admin)
|--------------------------------------------------------------------------
|
| Couvre les transitions de statut d'ArtistChangeRequest, l'application (ou
| non) du payload à l'artiste, les notifications à l'artiste et les garde-fous.
|
*/

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);

    $this->artistUser = User::factory()->artist()->create();
    $this->artist = Artist::factory()->published()->create([
        'user_id' => $this->artistUser->id,
        'artist_name' => 'Avant',
        'biography' => '<p>Ancienne bio.</p>',
    ]);
});

/**
 * @return Testable
 */
function editChange(ArtistChangeRequest $change)
{
    return Livewire::test(EditArtistChangeRequest::class, ['record' => $change->id]);
}

function pendingChangeFor(Artist $artist, User $submitter, array $payload): ArtistChangeRequest
{
    return ArtistChangeRequest::factory()->create([
        'artist_id' => $artist->id,
        'submitted_by' => $submitter->id,
        'payload' => $payload,
        'status' => ArtistChangeRequestStatus::PENDING->value,
    ]);
}

it('approving a change applies the payload, marks approved and notifies the artist', function () {
    Notification::fake();

    $change = pendingChangeFor($this->artist, $this->artistUser, [
        'artist_name' => 'Après',
        'biography' => '<p>Nouvelle bio.</p>',
    ]);

    editChange($change)->callAction('approve', ['notes' => null]);

    expect($change->fresh()->status)->toBe(ArtistChangeRequestStatus::APPROVED)
        ->and($this->artist->fresh()->artist_name)->toBe('Après')
        ->and($this->artist->fresh()->biography)->toBe('<p>Nouvelle bio.</p>');

    Notification::assertSentTo($this->artistUser, ChangeRequestDecisionNotification::class);
});

it('rejecting a change does NOT apply the payload but still notifies the artist', function () {
    Notification::fake();

    $change = pendingChangeFor($this->artist, $this->artistUser, [
        'artist_name' => 'Ne doit pas être appliqué',
    ]);

    editChange($change)->callAction('reject', ['notes' => 'Non conforme.']);

    expect($change->fresh()->status)->toBe(ArtistChangeRequestStatus::REJECTED)
        ->and($this->artist->fresh()->artist_name)->toBe('Avant');

    Notification::assertSentTo($this->artistUser, ChangeRequestDecisionNotification::class);
});

it('requesting adjustments requires a message and does not apply the payload', function () {
    $change = pendingChangeFor($this->artist, $this->artistUser, ['artist_name' => 'Nope']);

    editChange($change)
        ->callAction('requestChanges', ['notes' => ''])
        ->assertHasActionErrors(['notes' => 'required']);

    expect($change->fresh()->status)->toBe(ArtistChangeRequestStatus::PENDING);

    editChange($change)
        ->callAction('requestChanges', ['notes' => 'Merci de revoir le titre.'])
        ->assertHasNoActionErrors();

    expect($change->fresh()->status)->toBe(ArtistChangeRequestStatus::CHANGES_REQUESTED)
        ->and($this->artist->fresh()->artist_name)->toBe('Avant');
});

it("includes the reviewer's message (review_notes) in the decision notification", function () {
    $change = pendingChangeFor($this->artist, $this->artistUser, ['artist_name' => 'X']);
    $change->reject($this->admin, 'Motif détaillé du refus.');

    $mail = (new ChangeRequestDecisionNotification($change->fresh()))
        ->toMail($this->artistUser);

    expect($mail->introLines)->toContain("**Message de l'administrateur :** Motif détaillé du refus.");
});

it('hides review actions once a change request is no longer pending', function (ArtistChangeRequestStatus $status) {
    $change = ArtistChangeRequest::factory()->create([
        'artist_id' => $this->artist->id,
        'submitted_by' => $this->artistUser->id,
        'status' => $status,
    ]);

    editChange($change)
        ->assertActionDoesNotExist('approve')
        ->assertActionDoesNotExist('reject')
        ->assertActionDoesNotExist('requestChanges');
})->with([
    'approved' => ArtistChangeRequestStatus::APPROVED,
    'rejected' => ArtistChangeRequestStatus::REJECTED,
    'changes requested' => ArtistChangeRequestStatus::CHANGES_REQUESTED,
]);

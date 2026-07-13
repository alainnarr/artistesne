<?php

use App\Enums\ApprovalStatus;
use App\Filament\Resources\ArtistChangeRequests\Pages\EditArtistChangeRequest;
use App\Filament\Resources\ArtistChangeRequests\Pages\ListArtistChangeRequests;
use App\Filament\Resources\ArtistRegistrationRequests\Pages\EditArtistRegistrationRequest;
use App\Filament\Resources\ArtistRegistrationRequests\Pages\ListArtistRegistrationRequests;
use App\Models\Artist;
use App\Models\ArtistChangeRequest;
use App\Models\ArtistRegistrationRequest;
use App\Models\User;
use App\Notifications\MagicLinkNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('lists pending registration requests for admins', function () {
    $req = ArtistRegistrationRequest::factory()->create(['artist_name' => 'Demande Test']);

    Livewire::test(ListArtistRegistrationRequests::class)
        ->assertCanSeeTableRecords([$req]);
});

it('approves a registration: creates user, artist, and sends magic link', function () {
    Notification::fake();

    $req = ArtistRegistrationRequest::factory()->create([
        'artist_name' => 'Nouvelle Artiste',
        'email' => 'new-artist@inventaire.test',
    ]);

    Livewire::test(EditArtistRegistrationRequest::class, ['record' => $req->id])
        ->callAction('approve', ['notes' => 'Bienvenue']);

    $req->refresh();
    expect($req->status)->toBe(ApprovalStatus::Approved);
    expect($req->reviewed_by)->toBe($this->admin->id);

    $user = User::where('email', 'new-artist@inventaire.test')->first();
    expect($user)->not->toBeNull();
    expect($user->isArtist())->toBeTrue();
    expect($user->artist)->not->toBeNull();
    expect($user->artist->slug)->toBe('nouvelle-artiste');

    Notification::assertSentTo($user, MagicLinkNotification::class);
});

it('rejects a registration with a reason', function () {
    $req = ArtistRegistrationRequest::factory()->create();

    Livewire::test(EditArtistRegistrationRequest::class, ['record' => $req->id])
        ->callAction('reject', ['notes' => 'Hors périmètre']);

    expect($req->fresh()->status)->toBe(ApprovalStatus::Rejected);
});

it('approves a change request and applies the payload to the artist', function () {
    $artist = Artist::factory()->create(['name' => 'Old', 'biography' => '<p>Avant.</p>']);
    $change = ArtistChangeRequest::factory()->create([
        'artist_id' => $artist->id,
        'submitted_by' => User::factory()->artist()->create()->id,
        'payload' => ['name' => 'Nouveau', 'biography' => '<p>Après.</p>'],
        'status' => ApprovalStatus::Pending,
    ]);

    Livewire::test(EditArtistChangeRequest::class, ['record' => $change->id])
        ->callAction('approve');

    expect($change->fresh()->status)->toBe(ApprovalStatus::Approved);
    expect($artist->fresh()->name)->toBe('Nouveau');
    expect($artist->fresh()->biography)->toBe('<p>Après.</p>');
});

it('lists pending change requests', function () {
    $change = ArtistChangeRequest::factory()->create(['status' => ApprovalStatus::Pending]);

    Livewire::test(ListArtistChangeRequests::class)
        ->assertCanSeeTableRecords([$change]);
});

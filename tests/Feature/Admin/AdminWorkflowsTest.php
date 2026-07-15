<?php

use App\Database\Models\Artist;
use App\Database\Models\ArtistChangeRequest;
use App\Database\Models\Discipline;
use App\Database\Models\Registration;
use App\Database\Models\User;
use App\Enums\ApprovalStatus;
use App\Enums\RegistrationStatus;
use App\Filament\Resources\ArtistChangeRequests\Pages\EditArtistChangeRequest;
use App\Filament\Resources\ArtistChangeRequests\Pages\ListArtistChangeRequests;
use App\Filament\Resources\Registrations\Pages\ListRegistrations;
use App\Filament\Resources\Registrations\Pages\ViewRegistration;
use App\Notifications\MagicLinkNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('lists pending registrations for admins', function () {
    $musique = Discipline::where('code', 'musique')->firstOrFail();

    $reg = Registration::create([
        'real_name' => 'Demande Test',
        'artist_name' => 'Demande Test',
        'birth_date' => now()->subYears(25)->toDateString(),
        'email' => 'demande.test@inventaire.test',
        'phone' => '+41791234567',
        'residence_location' => 'Neuchâtel',
        'discipline_main' => $musique->id,
        'enum_status' => RegistrationStatus::OPEN->value,
    ]);

    Livewire::test(ListRegistrations::class)
        ->assertCanSeeTableRecords([$reg]);
});

it('approves a registration: creates user, artist, and sends magic link', function () {
    Notification::fake();

    $musique = Discipline::where('code', 'musique')->firstOrFail();

    $reg = Registration::create([
        'real_name' => 'Nouvelle Artiste',
        'artist_name' => 'Nouvelle Artiste',
        'birth_date' => now()->subYears(25)->toDateString(),
        'email' => 'new-artist@inventaire.test',
        'phone' => '+41791234567',
        'residence_location' => 'Neuchâtel',
        'discipline_main' => $musique->id,
        'enum_status' => RegistrationStatus::OPEN->value,
    ]);

    Livewire::test(ViewRegistration::class, ['record' => $reg->id])
        ->callAction('approve', ['notes' => 'Bienvenue']);

    $reg->refresh();
    expect($reg->enum_status)->toBe(RegistrationStatus::APPROVED);
    expect($reg->reviewed_by)->toBe($this->admin->id);

    $user = User::where('email', 'new-artist@inventaire.test')->first();
    expect($user)->not->toBeNull();
    expect($user->isArtist())->toBeTrue();
    expect($user->artist)->not->toBeNull();

    Notification::assertSentTo($user, MagicLinkNotification::class);
});

it('rejects a registration with a reason', function () {
    $musique = Discipline::where('code', 'musique')->firstOrFail();

    $reg = Registration::create([
        'real_name' => 'Refus Test',
        'artist_name' => 'Refus Test',
        'birth_date' => now()->subYears(25)->toDateString(),
        'email' => 'refus@inventaire.test',
        'phone' => '+41791234567',
        'residence_location' => 'Neuchâtel',
        'discipline_main' => $musique->id,
        'enum_status' => RegistrationStatus::OPEN->value,
    ]);

    Livewire::test(ViewRegistration::class, ['record' => $reg->id])
        ->callAction('reject', ['notes' => 'Hors périmètre']);

    expect($reg->fresh()->enum_status)->toBe(RegistrationStatus::REJECTED);
});

it('approves a change request and applies the payload to the artist', function () {
    $artist = Artist::factory()->create(['artist_name' => 'Old', 'biography' => '<p>Avant.</p>']);
    $change = ArtistChangeRequest::factory()->create([
        'artist_id' => $artist->id,
        'submitted_by' => User::factory()->artist()->create()->id,
        'payload' => ['artist_name' => 'Nouveau', 'biography' => '<p>Après.</p>'],
        'status' => ApprovalStatus::Pending->value,
    ]);

    Livewire::test(EditArtistChangeRequest::class, ['record' => $change->id])
        ->callAction('approve');

    expect($change->fresh()->status)->toBe(ApprovalStatus::Approved);
    expect($artist->fresh()->artist_name)->toBe('Nouveau');
    expect($artist->fresh()->biography)->toBe('<p>Après.</p>');
});

it('lists pending change requests', function () {
    $change = ArtistChangeRequest::factory()->create(['status' => ApprovalStatus::Pending->value]);

    Livewire::test(ListArtistChangeRequests::class)
        ->assertCanSeeTableRecords([$change]);
});

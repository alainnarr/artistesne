<?php

use App\Database\Models\Artist;
use App\Database\Models\ArtistChangeRequest;
use App\Database\Models\User;
use App\Enums\ArtistChangeRequestStatus;
use App\Livewire\Artist\EditProfile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Livewire;

it('renders the Espace Artistes hub for anyone', function () {
    $this->get(route('artist.login'))
        ->assertOk()
        ->assertSee('Référencement')
        ->assertSee('Créer un profil artiste');
});

it('creates a change request when the artist edits their page', function () {
    $user = User::factory()->artist()->create();
    $artist = Artist::factory()->create([
        'user_id' => $user->id,
        'artist_name' => 'Old Name',
        'biography' => '<p>Ancienne bio.</p>',
    ]);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('artist_name', 'New Name')
        ->set('biography', "Nouvelle bio.\n\nDeuxième paragraphe.")
        ->call('save')
        ->assertSet('submitted', true)
        ->assertHasNoErrors();

    expect(ArtistChangeRequest::count())->toBe(1);
    $change = ArtistChangeRequest::first();
    expect($change->payload)->toMatchArray(['artist_name' => 'New Name']);
    expect($change->payload['biography'] ?? null)->toContain('<p>Nouvelle bio.</p>');
    expect($change->status)->toBe(ArtistChangeRequestStatus::PENDING);
});

it('blocks submission when a pending change already exists', function () {
    $user = User::factory()->artist()->create();
    $artist = Artist::factory()->create(['user_id' => $user->id]);
    ArtistChangeRequest::factory()->create([
        'artist_id' => $artist->id,
        'submitted_by' => $user->id,
        'status' => ArtistChangeRequestStatus::PENDING->value,
    ]);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('artist_name', 'Whatever')
        ->set('biography', 'Bio')
        ->call('save')
        ->assertHasErrors('biography');

    expect(ArtistChangeRequest::count())->toBe(1);
});

it('rejects submissions with no actual changes', function () {
    $user = User::factory()->artist()->create();
    Artist::factory()->create([
        'user_id' => $user->id,
        'artist_name' => 'Same',
        'biography' => '<p>Bio.</p>',
        'links' => [],
    ]);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->call('save')
        ->assertHasErrors('biography');

    expect(ArtistChangeRequest::count())->toBe(0);
});

it('rejects access to the artist portal for non-artists', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('artist.profile-edit'))->assertRedirect(route('artist.login'));
});

it('saves a photo directly without creating a change request for it', function () {
    $user = User::factory()->artist()->create();
    Artist::factory()->create([
        'user_id' => $user->id,
        'biography' => '<p>Bio existante.</p>',
        'artist_name' => 'Ancien nom',
    ]);

    $this->actingAs($user);

    $fakePhoto = TemporaryUploadedFile::fake()->image('portrait.jpg', 500, 600);

    Livewire::test(EditProfile::class)
        ->set('photo', $fakePhoto)
        ->set('artist_name', 'Nouveau nom')
        ->set('biography', 'Bio existante.')
        ->call('save')
        ->assertSet('submitted', true)
        ->assertHasNoErrors();

    // Photo saved directly on the artist — not bundled into the change request.
    expect($user->fresh()->artist->rep_image)->not->toBeNull();

    $changeRequest = ArtistChangeRequest::first();
    expect($changeRequest)->not->toBeNull();
    expect($changeRequest->payload)->not->toHaveKey('rep_image');
});

it('rejects a javascript: URL in edit profile links', function () {
    $user = User::factory()->artist()->create();
    Artist::factory()->create(['user_id' => $user->id, 'biography' => '<p>Bio.</p>']);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('artist_name', 'Artiste Test')
        ->set('biography', 'Bio valide.')
        ->set('links', [['label' => 'Evil', 'url' => 'javascript:void(0)']])
        ->call('save')
        ->assertHasErrors(['links.0.url']);
});

it('shows a 2-step wizard and validates step 1 before advancing', function () {
    $user = User::factory()->artist()->create();
    Artist::factory()->create(['user_id' => $user->id, 'artist_name' => 'Old Name']);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->assertSet('currentStep', 1)
        ->set('artist_name', '')
        ->call('nextStep')
        ->assertHasErrors('artist_name')
        ->assertSet('currentStep', 1)
        ->set('artist_name', 'New Name')
        ->set('biography', 'Bio valide pour passer.')
        ->call('nextStep')
        ->assertHasNoErrors()
        ->assertSet('currentStep', 2)
        ->call('previousStep')
        ->assertSet('currentStep', 1);
});

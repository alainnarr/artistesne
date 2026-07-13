<?php

use App\Enums\ApprovalStatus;
use App\Livewire\Artist\EditProfile;
use App\Models\Artist;
use App\Models\ArtistChangeRequest;
use App\Models\User;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Livewire;

it('renders the artist dashboard for a linked artist', function () {
    $user = User::factory()->artist()->create();
    Artist::factory()->published()->create(['user_id' => $user->id, 'name' => 'Jane Doe']);

    $this->actingAs($user)->get(route('artist.dashboard'))
        ->assertOk()
        ->assertSee('Mon profil public')
        ->assertSee('Voir ma page publique');
});

it('warns when the artist account is not linked to an artist page', function () {
    $user = User::factory()->artist()->create();

    $this->actingAs($user)->get(route('artist.dashboard'))
        ->assertOk()
        ->assertSee('Se faire référencer sur la plateforme')
        ->assertSee('Créer un profil artiste');
});

it('shows a pending change banner', function () {
    $user = User::factory()->artist()->create();
    $artist = Artist::factory()->published()->create(['user_id' => $user->id]);
    ArtistChangeRequest::factory()->create([
        'artist_id' => $artist->id,
        'submitted_by' => $user->id,
        'status' => ApprovalStatus::Pending,
    ]);

    $this->actingAs($user)->get(route('artist.dashboard'))
        ->assertSee('Modification en cours de validation');
});

it('creates a change request when the artist edits their page', function () {
    $user = User::factory()->artist()->create();
    $artist = Artist::factory()->create([
        'user_id' => $user->id,
        'name' => 'Old Name',
        'biography' => '<p>Ancienne bio.</p>',
    ]);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('name', 'New Name')
        ->set('biographyText', "Nouvelle bio.\n\nDeuxième paragraphe.")
        ->call('save')
        ->assertSet('submitted', true)
        ->assertHasNoErrors();

    expect(ArtistChangeRequest::count())->toBe(1);
    $change = ArtistChangeRequest::first();
    expect($change->payload)->toMatchArray(['name' => 'New Name']);
    expect($change->payload['biography'] ?? null)->toContain('<p>Nouvelle bio.</p>');
    expect($change->status)->toBe(ApprovalStatus::Pending);
});

it('blocks submission when a pending change already exists', function () {
    $user = User::factory()->artist()->create();
    $artist = Artist::factory()->create(['user_id' => $user->id]);
    ArtistChangeRequest::factory()->create([
        'artist_id' => $artist->id,
        'submitted_by' => $user->id,
        'status' => ApprovalStatus::Pending,
    ]);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('name', 'Whatever')
        ->set('biographyText', 'Bio')
        ->call('save')
        ->assertHasErrors('biographyText');

    expect(ArtistChangeRequest::count())->toBe(1);
});

it('rejects submissions with no actual changes', function () {
    $user = User::factory()->artist()->create();
    Artist::factory()->create([
        'user_id' => $user->id,
        'name' => 'Same',
        'biography' => '<p>Bio.</p>',
        'discipline' => 'Peinture',
        'links' => [],
    ]);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->call('save')
        ->assertHasErrors('biographyText');

    expect(ArtistChangeRequest::count())->toBe(0);
});

it('rejects access to the artist portal for non-artists', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('artist.dashboard'))->assertRedirect(route('artist.login'));
});

it('saves a photo directly without creating a change request for it', function () {
    $user = User::factory()->artist()->create();
    Artist::factory()->create([
        'user_id' => $user->id,
        'biography' => '<p>Bio existante.</p>',
        'name' => 'Ancien nom',
        'cover_image' => null,
    ]);

    $this->actingAs($user);

    $fakePhoto = TemporaryUploadedFile::fake()->image('portrait.jpg', 500, 600);

    Livewire::test(EditProfile::class)
        ->set('photo', $fakePhoto)
        ->set('name', 'Nouveau nom')
        ->set('biographyText', 'Bio existante.')
        ->call('save')
        ->assertSet('submitted', true)
        ->assertHasNoErrors();

    // Photo saved directly on the artist — not bundled into the change request.
    expect($user->fresh()->artist->cover_image)->not->toBeNull();

    $changeRequest = ArtistChangeRequest::first();
    expect($changeRequest)->not->toBeNull();
    expect($changeRequest->payload)->not->toHaveKey('cover_image');
});

it('rejects a javascript: URL in edit profile links', function () {
    $user = User::factory()->artist()->create();
    Artist::factory()->create(['user_id' => $user->id, 'biography' => '<p>Bio.</p>']);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('name', 'Artiste Test')
        ->set('biographyText', 'Bio valide.')
        ->set('links', [['label' => 'Evil', 'url' => 'javascript:void(0)']])
        ->call('save')
        ->assertHasErrors(['links.0.url']);
});

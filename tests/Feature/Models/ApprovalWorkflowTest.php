<?php

use App\Enums\ApprovalStatus;
use App\Enums\ArtistStatus;
use App\Enums\UserRole;
use App\Models\Artist;
use App\Models\ArtistChangeRequest;
use App\Models\ArtistRegistrationRequest;
use App\Models\User;

it('casts user role enum and exposes helpers', function () {
    $admin = User::factory()->admin()->create();
    $artist = User::factory()->artist()->create();

    expect($admin->role)->toBe(UserRole::Admin);
    expect($admin->isAdmin())->toBeTrue();
    expect($artist->isArtist())->toBeTrue();
    expect($artist->password)->toBeNull();
});

it('only allows admins on the filament panel', function () {
    $admin = User::factory()->admin()->create();
    $artist = User::factory()->artist()->create();
    $panel = Filament\Facades\Filament::getPanel('admin');

    expect($admin->canAccessPanel($panel))->toBeTrue();
    expect($artist->canAccessPanel($panel))->toBeFalse();
});

it('scopes published artists', function () {
    Artist::factory()->published()->count(2)->create();
    Artist::factory()->count(3)->create();

    expect(Artist::published()->count())->toBe(2);
    expect(Artist::count())->toBe(5);
});

it('approves a registration request via the trait', function () {
    $admin = User::factory()->admin()->create();
    $request = ArtistRegistrationRequest::factory()->create();

    $request->approve($admin, 'Bienvenue');

    expect($request->fresh())
        ->status->toBe(ApprovalStatus::Approved)
        ->reviewed_by->toBe($admin->id)
        ->review_notes->toBe('Bienvenue')
        ->and($request->fresh()->reviewed_at)->not->toBeNull();
});

it('applies a change request payload to the artist', function () {
    $artist = Artist::factory()->published()->create([
        'biography' => '<p>Old</p>',
    ]);
    $change = ArtistChangeRequest::factory()->create([
        'artist_id' => $artist->id,
        'submitted_by' => User::factory()->artist()->create()->id,
        'payload' => ['biography' => '<p>New</p>', 'discipline' => 'Photographie'],
    ]);

    $change->apply();

    expect($artist->fresh())
        ->biography->toBe('<p>New</p>')
        ->discipline->toBe('Photographie')
        ->status->toBe(ArtistStatus::Published);
});

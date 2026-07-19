<?php

declare(strict_types=1);

use App\Database\Models\Artist;
use App\Database\Models\User;
use App\Enums\ArtistStatus;
use App\Filament\Resources\Artists\Pages\ListArtists;
use App\Notifications\ProfileReactivatedNotification;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

/*
|--------------------------------------------------------------------------
| Action "Afficher" (publish) de la liste des artistes (persona admin)
|--------------------------------------------------------------------------
|
| Distingue la toute première publication (pas d'email) de la réactivation
| d'un profil déjà publié au moins une fois par le passé (email envoyé).
|
*/

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('publishes a brand-new artist without sending a reactivation email', function () {
    Notification::fake();

    $user = User::factory()->artist()->create();
    $artist = Artist::factory()->create([
        'user_id' => $user->id,
        'enum_status' => ArtistStatus::DRAFT->value,
        'published_at' => null,
    ]);

    Livewire::test(ListArtists::class)
        ->callAction(TestAction::make('publish')->table($artist));

    expect($artist->fresh())
        ->enum_status->toBe(ArtistStatus::PUBLISHED)
        ->published_at->not->toBeNull();

    Notification::assertNothingSent();
});

it('notifies the artist when republishing a previously-disabled profile', function () {
    Notification::fake();

    $user = User::factory()->artist()->create();
    $artist = Artist::factory()->create([
        'user_id' => $user->id,
        'artist_name' => 'Studio Test',
        'enum_status' => ArtistStatus::DRAFT->value,
        'published_at' => now()->subMonths(8),
    ]);

    Livewire::test(ListArtists::class)
        ->callAction(TestAction::make('publish')->table($artist));

    expect($artist->fresh())->enum_status->toBe(ArtistStatus::PUBLISHED);

    Notification::assertSentTo($user, ProfileReactivatedNotification::class);
});

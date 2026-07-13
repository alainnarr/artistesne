<?php

use App\Livewire\Artist\Auth\RequestMagicLink;
use App\Models\User;
use App\Notifications\ArtistRegistrationAccessNotification;
use App\Notifications\MagicLinkNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

it('renders the magic link form', function () {
    $this->get(route('artist.login'))->assertOk()->assertSee('Espace artistes');
});

it('sends a magic link notification to a known artist', function () {
    Notification::fake();
    $artist = User::factory()->artist()->create(['email' => 'jane@inventaire.test']);

    Livewire::test(RequestMagicLink::class)
        ->set('email', 'jane@inventaire.test')
        ->call('send')
        ->assertSet('sent', true);

    Notification::assertSentTo($artist, MagicLinkNotification::class);
    expect($artist->fresh()->last_magic_link_sent_at)->not->toBeNull();
});

it('sends a registration access link to an unknown email', function () {
    Notification::fake();

    Livewire::test(RequestMagicLink::class)
        ->set('email', 'nobody@example.com')
        ->call('send')
        ->assertSet('sent', true);

    Notification::assertSentOnDemand(ArtistRegistrationAccessNotification::class, function ($notification, $channels, $notifiable) {
        return $notifiable->routes['mail'] === 'nobody@example.com';
    });
});

it('sends a registration access link to admin emails', function () {
    Notification::fake();
    User::factory()->admin()->create(['email' => 'admin@inventaire.test']);

    Livewire::test(RequestMagicLink::class)
        ->set('email', 'admin@inventaire.test')
        ->call('send')
        ->assertSet('sent', true);

    Notification::assertSentOnDemand(ArtistRegistrationAccessNotification::class, function ($notification, $channels, $notifiable) {
        return $notifiable->routes['mail'] === 'admin@inventaire.test';
    });
});

it('throttles magic link requests within one minute', function () {
    Notification::fake();
    $artist = User::factory()->artist()->create([
        'email' => 'jane@inventaire.test',
        'last_magic_link_sent_at' => now()->subSeconds(10),
    ]);

    Livewire::test(RequestMagicLink::class)
        ->set('email', 'jane@inventaire.test')
        ->call('send');

    Notification::assertNothingSent();
});

it('logs an artist in via a valid signed magic link', function () {
    $artist = User::factory()->artist()->create();
    $url = URL::temporarySignedRoute('artist.magic-link.consume', now()->addMinutes(10), ['user' => $artist->id]);

    $this->get($url)
        ->assertRedirect(route('artist.dashboard'));

    expect(auth()->id())->toBe($artist->id);
});

it('rejects an expired magic link', function () {
    $artist = User::factory()->artist()->create();
    $url = URL::temporarySignedRoute('artist.magic-link.consume', now()->subMinute(), ['user' => $artist->id]);

    $this->get($url)->assertRedirect(route('artist.login'));
    expect(auth()->check())->toBeFalse();
});

it('rejects a magic link consumed for an admin user id', function () {
    $admin = User::factory()->admin()->create();
    $url = URL::temporarySignedRoute('artist.magic-link.consume', now()->addMinutes(10), ['user' => $admin->id]);

    $this->get($url)->assertRedirect(route('artist.login'));
});

it('blocks artists from the admin panel', function () {
    $artist = User::factory()->artist()->create();

    $this->actingAs($artist)->get('/admin')->assertForbidden();
});

it('issues magic links that stay valid for one week (SPECS 2.3)', function () {
    // Default validity must be one week, not one hour.
    expect((new MagicLinkNotification)->expiresInMinutes)->toBe(60 * 24 * 7);

    $artist = User::factory()->artist()->create();
    $url = URL::temporarySignedRoute(
        'artist.magic-link.consume',
        now()->addMinutes((new MagicLinkNotification)->expiresInMinutes),
        ['user' => $artist->id],
    );

    // Still valid six days later.
    $this->travel(6)->days();
    $this->get($url)->assertRedirect(route('artist.dashboard'));
    expect(auth()->id())->toBe($artist->id);
});

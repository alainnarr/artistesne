<?php

use App\Enums\ArtistStatus;
use App\Models\Artist;
use App\Models\User;
use App\Notifications\ProfileAutoDisabledNotification;
use App\Notifications\SemiannualReminderFollowupNotification;
use App\Notifications\SemiannualReminderNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

it('sends semiannual reminder to artists who have not confirmed in 6 months', function () {
    Notification::fake();
    $user = User::factory()->artist()->create();
    $artist = Artist::factory()->for($user)->published()->create([
        'last_confirmed_at' => now()->subMonths(7),
        'reminder_sent_at' => null,
    ]);
    $this->artisan('artist:send-reminders')->assertSuccessful();
    Notification::assertSentTo($user, SemiannualReminderNotification::class);
    expect($artist->fresh()->confirmation_token)->not->toBeNull();
});

it('does not resend reminder to artists already in a reminder cycle', function () {
    Notification::fake();
    $user = User::factory()->artist()->create();
    Artist::factory()->for($user)->published()->create(['reminder_sent_at' => now()->subDays(5)]);
    $this->artisan('artist:send-reminders')->assertSuccessful();
    Notification::assertNothingSent();
});

it('sends followup after 21 days without confirmation', function () {
    Notification::fake();
    $user = User::factory()->artist()->create();
    Artist::factory()->for($user)->published()->create([
        'reminder_sent_at' => now()->subDays(22),
        'last_confirmed_at' => null,
        'confirmation_token' => 'tok',
    ]);
    $this->artisan('artist:send-reminder-followups')->assertSuccessful();
    Notification::assertSentTo($user, SemiannualReminderFollowupNotification::class);
});

it('disables artists who have not confirmed after 28 days', function () {
    Notification::fake();
    $user = User::factory()->artist()->create();
    $artist = Artist::factory()->for($user)->published()->create([
        'reminder_sent_at' => now()->subDays(29),
        'last_confirmed_at' => null,
        'confirmation_token' => 'tok',
    ]);
    $this->artisan('artist:disable-inactive')->assertSuccessful();
    expect($artist->fresh()->status)->toBe(ArtistStatus::Draft);
    Notification::assertSentTo($user, ProfileAutoDisabledNotification::class);
});

it('confirmation signed route marks artist confirmed and redirects to dashboard', function () {
    $user = User::factory()->artist()->create();
    $artist = Artist::factory()->for($user)->published()->create(['confirmation_token' => 'valid-tok']);
    $url = URL::signedRoute('artist.confirm-profile', ['token' => 'valid-tok']);
    $this->get($url)->assertRedirect(route('artist.dashboard'));
    expect($artist->fresh()->last_confirmed_at)->not->toBeNull();
    expect($artist->fresh()->confirmation_token)->toBeNull();
});

it('update signed route marks confirmed and redirects to edit profile', function () {
    $user = User::factory()->artist()->create();
    $artist = Artist::factory()->for($user)->published()->create(['confirmation_token' => 'update-tok']);
    $url = URL::signedRoute('artist.confirm-update', ['token' => 'update-tok']);
    $this->get($url)->assertRedirect(route('artist.profile.edit'));
    expect($artist->fresh()->last_confirmed_at)->not->toBeNull();
});

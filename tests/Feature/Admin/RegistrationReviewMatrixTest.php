<?php

declare(strict_types=1);

use App\Database\Models\Artist;
use App\Database\Models\Discipline;
use App\Database\Models\Registration;
use App\Database\Models\User;
use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Registrations\Pages\ViewRegistration;
use App\Notifications\MagicLinkNotification;
use App\Notifications\RegistrationRejectedNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

/*
|--------------------------------------------------------------------------
| Matrice de validation des inscriptions (persona admin)
|--------------------------------------------------------------------------
|
| Couvre les transitions de statut de Registration (OPEN -> APPROVED/REJECTED),
| les notifications associées et les garde-fous (actions masquées hors OPEN).
|
*/

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

function makeRegistration(array $overrides = []): Registration
{
    $musique = Discipline::where('code', 'musique')->firstOrFail();

    return Registration::create(array_merge([
        'real_name' => 'Camille Résonance',
        'artist_name' => 'Camille Résonance',
        'birth_date' => now()->subYears(28)->toDateString(),
        'email' => 'camille@inventaire.test',
        'phone' => '+41791234567',
        'residence_location' => 'Neuchâtel',
        'discipline_main' => $musique->id,
        'enum_status' => RegistrationStatus::OPEN->value,
    ], $overrides));
}

/**
 * @return Testable
 */
function viewRegistration(Registration $registration)
{
    return Livewire::test(ViewRegistration::class, ['record' => $registration->id]);
}

it('approval sends a single magic-link notification confirming approval, and stamps the throttle', function () {
    Notification::fake();

    $registration = makeRegistration();

    viewRegistration($registration)->callAction('approve', ['notes' => null]);

    $user = User::where('email', 'camille@inventaire.test')->firstOrFail();

    expect($user->role)->toBe(UserRole::ARTIST)
        ->and($user->password)->toBeNull()
        ->and($user->last_magic_link_sent_at)->not->toBeNull();

    Notification::assertSentTo($user, MagicLinkNotification::class);
    Notification::assertSentToTimes($user, MagicLinkNotification::class, 1);
});

it('approval sets the created artist discipline from the registration main domain', function () {
    $musique = Discipline::where('code', 'musique')->firstOrFail();

    $registration = makeRegistration([
        'artist_name' => 'Domaine Test',
        'email' => 'domaine@inventaire.test',
        'discipline_main' => $musique->id,
    ]);

    viewRegistration($registration)->callAction('approve', ['notes' => null]);

    $user = User::where('email', 'domaine@inventaire.test')->firstOrFail();

    expect($user->artist->discipline_main_id)->toBe($musique->id);
});

it('approval reuses an existing user with the same email instead of duplicating it', function () {
    $existing = User::factory()->artist()->create(['email' => 'reuse@inventaire.test']);

    $registration = makeRegistration([
        'artist_name' => 'Reuse Artist',
        'email' => 'reuse@inventaire.test',
    ]);

    viewRegistration($registration)->callAction('approve', ['notes' => null]);

    expect(User::where('email', 'reuse@inventaire.test')->count())->toBe(1)
        ->and(Artist::where('user_id', $existing->id)->count())->toBe(1);
});

it('rejection stamps reviewer metadata and notifies the applicant', function () {
    Notification::fake();

    $registration = makeRegistration([
        'artist_name' => 'Refus Test',
        'email' => 'refus@inventaire.test',
    ]);

    viewRegistration($registration)->callAction('reject', ['notes' => 'Hors périmètre cantonal.']);

    $registration->refresh();

    expect($registration->enum_status)->toBe(RegistrationStatus::REJECTED)
        ->and($registration->reviewed_by)->toBe($this->admin->id)
        ->and($registration->reviewed_at)->not->toBeNull()
        ->and($registration->review_notes)->toBe('Hors périmètre cantonal.');

    Notification::assertSentTimes(RegistrationRejectedNotification::class, 1);
});

it('hides review actions once a registration is no longer open', function (RegistrationStatus $status) {
    $registration = makeRegistration(['enum_status' => $status->value]);

    viewRegistration($registration)
        ->assertActionDoesNotExist('approve')
        ->assertActionDoesNotExist('reject');
})->with([
    'approved' => RegistrationStatus::APPROVED,
    'rejected' => RegistrationStatus::REJECTED,
]);

it('exposes review actions while a registration is open', function () {
    $registration = makeRegistration(['enum_status' => RegistrationStatus::OPEN->value]);

    viewRegistration($registration)
        ->assertActionExists('approve')
        ->assertActionExists('reject');
});

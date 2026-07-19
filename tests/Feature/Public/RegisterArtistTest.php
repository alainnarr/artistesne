<?php

use App\Database\Models\Discipline;
use App\Database\Models\Registration;
use App\Enums\RegistrationStatus;
use App\Livewire\Public\RegisterArtist;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

// ── Rendering ─────────────────────────────────────────────────────────────────

it('renders the registration form', function () {
    $this->get(route('public.artist-registration'))
        ->assertOk()
        ->assertSeeLivewire(RegisterArtist::class);
});

// ── Step 1 validation ────────────────────────────────────────────────────────

it('blocks advancing from step 1 when required fields are missing', function () {
    Livewire::test(RegisterArtist::class)
        ->set('full_name', '')
        ->set('email', '')
        ->call('nextStep')
        ->assertSet('currentStep', 1)
        ->assertHasErrors(['full_name' => 'required', 'email' => 'required']);
});

it('advances to step 2 when all step 1 fields are valid', function () {
    Livewire::test(RegisterArtist::class)
        ->set('full_name', 'Marie Dupont')
        ->set('birth_date', '1990-06-15')
        ->set('email', 'marie@example.com')
        ->set('phoneCountry', 'CH')
        ->set('phone', '+41791234567')
        ->set('locality', 'Neuchâtel')
        ->call('nextStep')
        ->assertSet('currentStep', 2)
        ->assertHasNoErrors();
});

// ── Step 2 validation ────────────────────────────────────────────────────────

it('blocks advancing from step 2 when main_domain is missing', function () {
    Livewire::test(RegisterArtist::class)
        ->set('currentStep', 2)
        ->set('main_domain', '')
        ->set('main_activity', '')
        ->call('nextStep')
        ->assertSet('currentStep', 2)
        ->assertHasErrors(['main_domain' => 'required', 'main_activity' => 'required']);
});

// ── Submission ────────────────────────────────────────────────────────────────

it('creates a registration on valid submission', function () {
    Notification::fake();

    Livewire::test(RegisterArtist::class)
        ->set('full_name', 'Marie Dupont')
        ->set('birth_date', '1990-06-15')
        ->set('email', 'marie@example.com')
        ->set('phoneCountry', 'CH')
        ->set('phone', '+41791234567')
        ->set('locality', 'Neuchâtel')
        ->set('main_domain', disciplineId('musique'))
        ->set('main_activity', activityId('musique.chanteur'))
        ->set('training', 'Conservatoire de Neuchâtel')
        ->set('paid_activity', 'Concerts réguliers')
        ->set('attests', true)
        ->call('submit')
        ->assertSet('submitted', true)
        ->assertHasNoErrors();

    expect(Registration::count())->toBe(1);

    $reg = Registration::first();
    expect($reg->email)->toBe('marie@example.com');
    expect($reg->enum_status)->toBe(RegistrationStatus::OPEN);
    expect($reg->real_name)->toBe('Marie Dupont');
});

it('does not create a duplicate for an already-pending email', function () {
    Notification::fake();

    // Existing open registration for the same email.
    Registration::create([
        'real_name' => 'Marie Dupont',
        'artist_name' => 'Marie Dupont',
        'birth_date' => '1990-06-15',
        'email' => 'marie@example.com',
        'phone' => '+41791234567',
        'residence_location' => 'Neuchâtel',
        'discipline_main' => Discipline::where('code', 'musique')->value('id'),
        'enum_status' => RegistrationStatus::OPEN->value,
    ]);

    Livewire::test(RegisterArtist::class)
        ->set('full_name', 'Marie Dupont')
        ->set('birth_date', '1990-06-15')
        ->set('email', 'marie@example.com')
        ->set('phoneCountry', 'CH')
        ->set('phone', '+41791234567')
        ->set('locality', 'Neuchâtel')
        ->set('main_domain', disciplineId('musique'))
        ->set('main_activity', activityId('musique.chanteur'))
        ->set('training', 'Conservatoire de Neuchâtel')
        ->set('paid_activity', 'Concerts réguliers')
        ->set('attests', true)
        ->call('submit')
        ->assertHasErrors(['email'])
        ->assertSet('submitted', false);

    // Still 1 — no duplicate created.
    expect(Registration::count())->toBe(1);
});

it('rejects a javascript: URL in registration links', function () {
    Notification::fake();

    Livewire::test(RegisterArtist::class)
        ->set('full_name', 'Test')
        ->set('birth_date', '1990-01-01')
        ->set('email', 'test@example.com')
        ->set('phoneCountry', 'CH')
        ->set('phone', '+41791234567')
        ->set('locality', 'Neuchâtel')
        ->set('main_domain', disciplineId('musique'))
        ->set('main_activity', activityId('musique.chanteur'))
        ->set('training', 'Formation A')
        ->set('paid_activity', 'Activité B')
        ->set('links', ['javascript:alert(1)'])
        ->set('attests', true)
        ->call('submit')
        ->assertHasErrors(['links.0']);
});

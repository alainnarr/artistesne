<?php

use App\Database\Models\Activity;
use App\Database\Models\Discipline;
use App\Database\Models\Registration;
use App\Enums\RegistrationStatus;
use App\Livewire\Public\RegisterArtist;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

it('renders the home page', function () {
    $this->get(route('home'))->assertOk()->assertSee('Annuaire des artistes');
});

it('renders the about page', function () {
    $this->get(route('about'))
        ->assertOk()
        ->assertSee('À propos de', false)
        ->assertSee('Artistes.ne', false);
});

it('exposes the public artist registration form without a signed link', function () {
    $this->get(route('artist.register'))
        ->assertOk()
        ->assertSee('Demande de', false)
        ->assertSee('référencement', false);
});

it('accepts an artist registration through the wizard', function () {
    Notification::fake();

    $musique = Discipline::where('code', 'musique')->firstOrFail();
    $chanteur = Activity::where('code', 'musique.chanteur')->firstOrFail();

    Livewire::test(RegisterArtist::class)
        // Étape 1
        ->set('full_name', 'Jean Dupont')
        ->set('artist_name', 'Nouveau Talent')
        ->set('birth_date', '1990-01-01')
        ->set('email', 'nouveau@inventaire.test')
        ->set('phoneCountry', 'CH')
        ->set('phone', '+41 79 123 45 67')
        ->set('locality', 'Neuchâtel')
        ->call('nextStep')
        ->assertHasNoErrors()
        ->assertSet('currentStep', 2)
        // Étape 2
        ->set('main_domain', (string) $musique->id)
        ->set('main_activity', (string) $chanteur->id)
        ->set('training', 'Bachelor en arts visuels, ECAL, 2015')
        ->set('paid_activity', "Vente d'œuvres et commandes publiques")
        ->set('last_activity', '2024 — Neuchâtel')
        ->call('nextStep')
        ->assertHasNoErrors()
        ->assertSet('currentStep', 3)
        // Étape 3
        ->set('attests', true)
        ->call('submit')
        ->assertSet('submitted', true)
        ->assertHasNoErrors();

    $reg = Registration::where('email', 'nouveau@inventaire.test')->first();

    expect($reg)->not->toBeNull()
        ->and($reg->enum_status)->toBe(RegistrationStatus::OPEN)
        ->and($reg->real_name)->toBe('Jean Dupont');
});

it('blocks moving to the next step when required fields are missing', function () {
    Livewire::test(RegisterArtist::class)
        ->set('full_name', '')
        ->call('nextStep')
        ->assertHasErrors(['full_name' => 'required'])
        ->assertSet('currentStep', 1);
});

it('rejects a phone number with an invalid format', function () {
    Livewire::test(RegisterArtist::class)
        ->set('full_name', 'Mauvais Numéro')
        ->set('birth_date', '1990-01-01')
        ->set('email', 'mauvais@inventaire.test')
        ->set('phoneCountry', 'CH')
        ->set('phone', '12 34')
        ->set('locality', 'Neuchâtel')
        ->call('nextStep')
        ->assertHasErrors(['phone'])
        ->assertSet('currentStep', 1);
});

it('accepts a phone number matching the selected country format', function () {
    Livewire::test(RegisterArtist::class)
        ->set('full_name', 'Bon Numéro')
        ->set('birth_date', '1990-01-01')
        ->set('email', 'bon@inventaire.test')
        ->set('phoneCountry', 'CH')
        ->set('phone', '+41 79 123 45 67')
        ->set('locality', 'Neuchâtel')
        ->call('nextStep')
        ->assertHasNoErrors(['phone'])
        ->assertSet('currentStep', 2);
});

it('accepts a phone number for a non-swiss country', function () {
    Livewire::test(RegisterArtist::class)
        ->set('full_name', 'International Number')
        ->set('birth_date', '1990-01-01')
        ->set('email', 'international@inventaire.test')
        ->set('phoneCountry', 'US')
        ->set('phone', '+1 202 555 0123')
        ->set('locality', 'Neuchâtel')
        ->call('nextStep')
        ->assertHasNoErrors(['phone', 'phoneCountry'])
        ->assertSet('currentStep', 2);
});

it('requires commune and canton link when residing outside the canton', function () {
    Livewire::test(RegisterArtist::class)
        ->set('full_name', 'Hors Canton')
        ->set('birth_date', '1985-06-15')
        ->set('email', 'horscanton@inventaire.test')
        ->set('phone', '79 000 00 00')
        ->set('locality', 'Hors canton')
        ->call('nextStep')
        ->assertHasErrors(['commune', 'canton_link'])
        ->assertSet('currentStep', 1);
});

it('exposes activities filtered by the selected domain', function () {
    $musique = Discipline::where('code', 'musique')->firstOrFail();

    Livewire::test(RegisterArtist::class)
        ->set('main_domain', (string) $musique->id)
        ->assertSet('main_activity', '')
        ->assertSee('Chanteur-euse');
});

it('requires a recent achievement when fewer than two criteria are filled', function () {
    $musique = Discipline::where('code', 'musique')->firstOrFail();
    $chanteur = Activity::where('code', 'musique.chanteur')->firstOrFail();

    Livewire::test(RegisterArtist::class)
        ->set('full_name', 'Un Critère')
        ->set('birth_date', '1990-01-01')
        ->set('email', 'critere@inventaire.test')
        ->set('phone', '79 111 11 11')
        ->set('locality', 'Neuchâtel')
        ->call('nextStep')
        ->assertSet('currentStep', 2)
        ->set('main_domain', (string) $musique->id)
        ->set('main_activity', (string) $chanteur->id)
        ->set('training', 'Conservatoire de Lausanne')
        ->set('last_activity', '2024 — Berne')
        ->call('nextStep')
        ->assertHasErrors(['recent_achievement']);
});

it('does not duplicate a pending registration', function () {
    Notification::fake();

    $musique = Discipline::where('code', 'musique')->firstOrFail();
    $chanteur = Activity::where('code', 'musique.chanteur')->firstOrFail();

    Registration::create([
        'real_name' => 'Jean Dup',
        'artist_name' => 'Dup',
        'birth_date' => '1980-03-03',
        'email' => 'dup@inventaire.test',
        'phone' => '+41791234567',
        'residence_location' => 'Neuchâtel',
        'discipline_main' => $musique->id,
        'enum_status' => RegistrationStatus::OPEN->value,
    ]);

    Livewire::test(RegisterArtist::class)
        ->set('full_name', 'Jean Dup')
        ->set('birth_date', '1980-03-03')
        ->set('email', 'dup@inventaire.test')
        ->set('phone', '+41791234567')
        ->set('locality', 'Neuchâtel')
        ->call('nextStep')
        ->set('main_domain', (string) $musique->id)
        ->set('main_activity', (string) $chanteur->id)
        ->set('training', 'Conservatoire de Lausanne')
        ->set('paid_activity', '50 concerts par an')
        ->call('nextStep')
        ->set('attests', true)
        ->call('submit')
        ->assertHasErrors(['email'])
        ->assertSet('submitted', false);

    expect(Registration::where('email', 'dup@inventaire.test')->count())->toBe(1);
});

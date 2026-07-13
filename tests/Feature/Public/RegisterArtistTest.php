<?php

use App\Enums\ApprovalStatus;
use App\Livewire\Public\RegisterArtist;
use App\Models\ArtistRegistrationRequest;
use Livewire\Livewire;

it('renders the registration form', function () {
    $this->get(route('artist.register'))
        ->assertOk()
        ->assertSeeLivewire(RegisterArtist::class);
});

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

it('blocks advancing from step 2 when main_domain is missing', function () {
    Livewire::test(RegisterArtist::class)
        ->set('currentStep', 2)
        ->set('main_domain', '')
        ->set('main_activity', '')
        ->call('nextStep')
        ->assertSet('currentStep', 2)
        ->assertHasErrors(['main_domain' => 'required', 'main_activity' => 'required']);
});

it('creates a registration request on valid submission', function () {
    Livewire::test(RegisterArtist::class)
        ->set('full_name', 'Marie Dupont')
        ->set('birth_date', '1990-06-15')
        ->set('email', 'marie@example.com')
        ->set('phoneCountry', 'CH')
        ->set('phone', '+41791234567')
        ->set('locality', 'Neuchâtel')
        ->set('main_domain', 'musique')
        ->set('main_activity', 'Compositeur·trice')
        ->set('training', 'Conservatoire de Neuchâtel')
        ->set('paid_activity', 'Concerts réguliers')
        ->set('attests', true)
        ->call('submit')
        ->assertSet('submitted', true)
        ->assertHasNoErrors();

    expect(ArtistRegistrationRequest::count())->toBe(1);
    $req = ArtistRegistrationRequest::first();
    expect($req->email)->toBe('marie@example.com');
    expect($req->status)->toBe(ApprovalStatus::Pending);
});

it('does not create a duplicate request for an already pending email', function () {
    ArtistRegistrationRequest::factory()->create([
        'email' => 'marie@example.com',
        'status' => ApprovalStatus::Pending,
    ]);

    Livewire::test(RegisterArtist::class)
        ->set('full_name', 'Marie Dupont')
        ->set('birth_date', '1990-06-15')
        ->set('email', 'marie@example.com')
        ->set('phoneCountry', 'CH')
        ->set('phone', '+41791234567')
        ->set('locality', 'Neuchâtel')
        ->set('main_domain', 'musique')
        ->set('main_activity', 'Compositeur·trice')
        ->set('training', 'Conservatoire de Neuchâtel')
        ->set('paid_activity', 'Concerts réguliers')
        ->set('attests', true)
        ->call('submit')
        ->assertSet('submitted', true);

    expect(ArtistRegistrationRequest::count())->toBe(1);
});

it('rejects a javascript: URL in registration links', function () {
    Livewire::test(RegisterArtist::class)
        ->set('full_name', 'Test')
        ->set('birth_date', '1990-01-01')
        ->set('email', 'test@example.com')
        ->set('phoneCountry', 'CH')
        ->set('phone', '+41791234567')
        ->set('locality', 'Neuchâtel')
        ->set('main_domain', 'musique')
        ->set('main_activity', 'Compositeur·trice')
        ->set('training', 'Formation A')
        ->set('paid_activity', 'Activité B')
        ->set('links', ['javascript:alert(1)'])
        ->set('attests', true)
        ->call('submit')
        ->assertHasErrors(['links.0']);
});

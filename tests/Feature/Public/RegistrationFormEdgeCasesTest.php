<?php

declare(strict_types=1);

use App\Livewire\Public\RegisterArtist;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

/*
|--------------------------------------------------------------------------
| Cas limites du formulaire de demande de référencement
|--------------------------------------------------------------------------
|
| Complète tests/Feature/Public/RegisterArtistTest.php avec une matrice
| rigoureuse : téléphone, territorialité hors canton, activité « autre »,
| règle des 2 critères sur 3, attestation, documents, liens, date de naissance.
|
*/

/**
 * Remplit une étape 1 valide de base, puis applique les surcharges fournies.
 *
 * @return Testable
 */
function registrationWithStep1(array $overrides = [])
{
    $component = Livewire::test(RegisterArtist::class)
        ->set('full_name', 'Marie Dupont')
        ->set('birth_date', '1990-06-15')
        ->set('email', 'marie@example.com')
        ->set('phoneCountry', 'CH')
        ->set('phone', '+41791234567')
        ->set('locality', 'Neuchâtel');

    foreach ($overrides as $key => $value) {
        $component->set($key, $value);
    }

    return $component;
}

// --- Téléphone : matrice de formats -----------------------------------------

it('accepts valid phone number formats', function (string $phone) {
    registrationWithStep1(['phone' => $phone])
        ->call('nextStep')
        ->assertHasNoErrors(['phone'])
        ->assertSet('currentStep', 2);
})->with([
    'CH mobile +' => '+41 79 123 45 67',
    'CH compact' => '+41791234567',
    'international spaces' => '+33 6 12 34 56 78',
    'local digits' => '0791234567',
]);

it('rejects invalid phone number formats', function (string $phone) {
    registrationWithStep1(['phone' => $phone])
        ->call('nextStep')
        ->assertHasErrors(['phone'])
        ->assertSet('currentStep', 1);
})->with([
    'too short' => '12345',
    'letters' => 'not-a-phone',
    'double plus' => '++41791234567',
    'plus not leading' => '41+791234567',
]);

// --- Territorialité : hors canton --------------------------------------------

it('requires commune and canton link when residing outside the canton', function () {
    registrationWithStep1([
        'locality' => config('localities.outside_canton_value'),
        'commune' => '',
        'canton_link' => '',
    ])
        ->call('nextStep')
        ->assertHasErrors(['commune' => 'required', 'canton_link' => 'required'])
        ->assertSet('currentStep', 1);
});

it('advances when outside-canton fields are provided', function () {
    registrationWithStep1([
        'locality' => config('localities.outside_canton_value'),
        'commune' => 'Yverdon-les-Bains',
        'canton_link' => 'Résidence secondaire et collaborations régulières à Neuchâtel.',
    ])
        ->call('nextStep')
        ->assertHasNoErrors()
        ->assertSet('currentStep', 2);
});

// --- Date de naissance -------------------------------------------------------

it('rejects a birth date in the future', function () {
    registrationWithStep1(['birth_date' => now()->addYear()->format('Y-m-d')])
        ->call('nextStep')
        ->assertHasErrors(['birth_date'])
        ->assertSet('currentStep', 1);
});

// --- Étape 2 : activité « autre » --------------------------------------------

it('requires a free-text activity when "autre" is selected', function () {
    Livewire::test(RegisterArtist::class)
        ->set('currentStep', 2)
        ->set('main_domain', 'musique')
        ->set('main_activity', config('taxonomy.other_value'))
        ->set('main_activity_other', '')
        ->set('training', 'Conservatoire')
        ->set('paid_activity', 'Concerts')
        ->call('nextStep')
        ->assertHasErrors(['main_activity_other' => 'required'])
        ->assertSet('currentStep', 2);
});

// --- Étape 2 : règle des 2 critères sur 3 ------------------------------------

it('requires a recent achievement when fewer than two criteria are filled', function () {
    Livewire::test(RegisterArtist::class)
        ->set('currentStep', 2)
        ->set('main_domain', 'musique')
        ->set('main_activity', 'Chanteur-euse')
        ->set('training', 'Conservatoire')   // 1 seul critère rempli
        ->set('paid_activity', '')
        ->set('recognition', '')
        ->set('recent_achievement', '')
        ->call('nextStep')
        ->assertHasErrors(['recent_achievement' => 'required'])
        ->assertSet('currentStep', 2);
});

it('does not require a recent achievement when at least two criteria are filled', function () {
    Livewire::test(RegisterArtist::class)
        ->set('currentStep', 2)
        ->set('main_domain', 'musique')
        ->set('main_activity', 'Chanteur-euse')
        ->set('training', 'Conservatoire')
        ->set('paid_activity', 'Concerts réguliers') // 2 critères remplis
        ->set('recent_achievement', '')
        ->call('nextStep')
        ->assertHasNoErrors(['recent_achievement'])
        ->assertSet('currentStep', 3);
});

// --- Étape 3 : attestation obligatoire ---------------------------------------

it('requires the accuracy attestation before submitting', function () {
    registrationWithStep1()
        ->set('main_domain', 'musique')
        ->set('main_activity', 'Chanteur-euse')
        ->set('training', 'Conservatoire')
        ->set('paid_activity', 'Concerts')
        ->set('attests', false)
        ->call('submit')
        ->assertHasErrors(['attests'])
        ->assertSet('submitted', false);
});

// --- Étape 3 : documents -----------------------------------------------------

it('rejects documents with a disallowed file type', function () {
    registrationWithStep1()
        ->set('main_domain', 'musique')
        ->set('main_activity', 'Chanteur-euse')
        ->set('training', 'Conservatoire')
        ->set('paid_activity', 'Concerts')
        ->set('documents', [UploadedFile::fake()->create('malware.exe', 100)])
        ->set('attests', true)
        ->call('submit')
        ->assertHasErrors(['documents.0']);
});

it('rejects documents larger than 5 MB', function () {
    registrationWithStep1()
        ->set('main_domain', 'musique')
        ->set('main_activity', 'Chanteur-euse')
        ->set('training', 'Conservatoire')
        ->set('paid_activity', 'Concerts')
        ->set('documents', [UploadedFile::fake()->create('portfolio.pdf', 6000)])
        ->set('attests', true)
        ->call('submit')
        ->assertHasErrors(['documents.0']);
});

// --- Étape 3 : liens ---------------------------------------------------------

it('rejects a non-URL value among the links', function () {
    registrationWithStep1()
        ->set('main_domain', 'musique')
        ->set('main_activity', 'Chanteur-euse')
        ->set('training', 'Conservatoire')
        ->set('paid_activity', 'Concerts')
        ->set('links', ['https://valid.example', 'pas-une-url'])
        ->set('attests', true)
        ->call('submit')
        ->assertHasErrors(['links.1']);
});

// --- Navigation --------------------------------------------------------------

it('can navigate back to the previous step', function () {
    registrationWithStep1()
        ->call('nextStep')
        ->assertSet('currentStep', 2)
        ->call('previousStep')
        ->assertSet('currentStep', 1);
});

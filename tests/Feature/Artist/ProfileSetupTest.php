<?php

use App\Livewire\Artist\ProfileSetup;
use App\Models\Artist;
use App\Models\User;
use Livewire\Livewire;

it('redirects guests away from the profile setup page', function () {
    $this->get(route('artist.profile.setup'))
        ->assertRedirect();
});

it('renders the profile setup wizard for an authenticated artist', function () {
    $user = User::factory()->artist()->create();
    $artist = Artist::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('artist.profile.setup'))
        ->assertOk()
        ->assertSee('Créez')
        ->assertSee('Activités');
});

it('saves biography, discipline and activities to the artist record', function () {
    $user = User::factory()->artist()->create();
    Artist::factory()->for($user)->create([
        'biography' => null,
        'discipline' => null,
        'activities' => [],
    ]);

    $this->actingAs($user);

    Livewire::test(ProfileSetup::class)
        ->set('biography', "Artiste pluridisciplinaire basée à Neuchâtel.\n\nDeuxième paragraphe.")
        ->set('discipline', 'Peinture')
        ->set('activities', ['Peintre', 'Illustrateur·trice'])
        ->call('save')
        ->assertSet('submitted', true)
        ->assertHasNoErrors();

    $artist = $user->fresh()->artist;
    expect($artist->biography)->toContain('<p>Artiste pluridisciplinaire');
    expect($artist->discipline)->toBe('Peinture');
    expect($artist->activities)->toBe(['Peintre', 'Illustrateur·trice']);
});

it('saves links and collaborations in step 2', function () {
    $user = User::factory()->artist()->create();
    Artist::factory()->for($user)->create(['links' => [], 'collaborations' => []]);

    $this->actingAs($user);

    Livewire::test(ProfileSetup::class)
        ->set('biography', 'Bio minimale pour passer la validation.')
        ->set('links', [['label' => 'Site web', 'url' => 'https://example.com']])
        ->set('collaborations', [['name' => 'Théâtre du Château', 'url' => 'https://theatre.ch']])
        ->set('display_contact_button', true)
        ->call('save')
        ->assertSet('submitted', true)
        ->assertHasNoErrors();

    $artist = $user->fresh()->artist;
    expect($artist->links)->toMatchArray([['label' => 'Site web', 'url' => 'https://example.com']]);
    expect($artist->collaborations)->toMatchArray([['name' => 'Théâtre du Château', 'url' => 'https://theatre.ch']]);
    expect($artist->display_contact_button)->toBeTrue();
});

it('requires biography to advance to step 2', function () {
    $user = User::factory()->artist()->create();
    Artist::factory()->for($user)->create();

    $this->actingAs($user);

    Livewire::test(ProfileSetup::class)
        ->set('biography', '')
        ->call('nextStep')
        ->assertSet('currentStep', 1)
        ->assertHasErrors(['biography' => 'required']);
});

it('rejects a javascript: URL in links', function () {
    $user = User::factory()->artist()->create();
    Artist::factory()->for($user)->create();

    $this->actingAs($user);

    Livewire::test(ProfileSetup::class)
        ->set('biography', 'Bio valide.')
        ->set('links', [['label' => 'Malveillant', 'url' => 'javascript:alert(1)']])
        ->call('save')
        ->assertHasErrors(['links.0.url']);
});

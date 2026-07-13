<?php

use App\Livewire\Public\ArtistsIndex;
use App\Models\Artist;
use Livewire\Livewire;

it('responds with 200 on /artistes', function () {
    $this->get(route('public.artists.index'))->assertOk();
});

it('shows published artists', function () {
    Artist::factory()->count(3)->published()->create();
    Artist::factory()->create(); // unpublished

    Livewire::test(ArtistsIndex::class)
        ->assertSee('Découvrir les artistes')
        ->assertSee('3 artistes référencé·es');
});

it('filters artists by search', function () {
    Artist::factory()->published()->create(['name' => 'Alice Martin']);
    Artist::factory()->published()->create(['name' => 'Bob Dupont']);

    Livewire::test(ArtistsIndex::class)
        ->set('search', 'Alice')
        ->assertSee('Alice Martin')
        ->assertDontSee('Bob Dupont');
});

it('filters artists by discipline via filter modal', function () {
    Artist::factory()->published()->create(['name' => 'Alice Martin', 'discipline' => 'peinture']);
    Artist::factory()->published()->create(['name' => 'Bob Dupont', 'discipline' => 'musique']);

    Livewire::test(ArtistsIndex::class)
        ->set('filterDomain', 'peinture')
        ->assertSee('Alice Martin')
        ->assertDontSee('Bob Dupont');
});

it('only shows main activities in the filter modal once a primary domain is selected', function () {
    Artist::factory()->published()->create([
        'discipline' => 'Musique',
        'activities' => ['Chanteur-euse'],
    ]);

    Livewire::test(ArtistsIndex::class)
        ->assertSee('Pour afficher les activités principales, vous devez sélectionner un domaine.')
        ->assertDontSeeHtml('wire:model.live="filterActivities"')
        ->set('filterDomain', 'Musique')
        ->assertDontSee('Pour afficher les activités principales, vous devez sélectionner un domaine.')
        ->assertSeeHtml('wire:model.live="filterActivities"');
});

it('sorts artists by name ascending by default', function () {
    Artist::factory()->published()->create(['name' => 'Zoé Bernard']);
    Artist::factory()->published()->create(['name' => 'Alice Martin']);

    Livewire::test(ArtistsIndex::class)
        ->assertSet('sort', 'name');
});

it('card links point to artist profile', function () {
    $artist = Artist::factory()->published()->create();

    Livewire::test(ArtistsIndex::class)
        ->assertSee(route('public.artist.show', $artist));
});

it('shows empty state when no artists match the search', function () {
    Artist::factory()->published()->create(['name' => 'Alice Martin']);

    Livewire::test(ArtistsIndex::class)
        ->set('search', 'zzznomatch')
        ->assertSee('Aucun résultat ne correspond à votre recherche');
});

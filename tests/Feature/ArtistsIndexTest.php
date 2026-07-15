<?php

use App\Database\Models\Artist;
use App\Database\Models\Discipline;
use App\Livewire\Public\ArtistsIndex;
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
    Artist::factory()->published()->create(['artist_name' => 'Alice Martin']);
    Artist::factory()->published()->create(['artist_name' => 'Bob Dupont']);

    Livewire::test(ArtistsIndex::class)
        ->set('search', 'Alice')
        ->assertSee('Alice Martin')
        ->assertDontSee('Bob Dupont');
});

it('filters artists by discipline via filter modal', function () {
    $peinture = Discipline::where('code', 'visuels')->firstOrFail();
    $musique = Discipline::where('code', 'musique')->firstOrFail();

    Artist::factory()->published()->create(['artist_name' => 'Alice Martin', 'discipline_main_id' => $peinture->id]);
    Artist::factory()->published()->create(['artist_name' => 'Bob Dupont', 'discipline_main_id' => $musique->id]);

    Livewire::test(ArtistsIndex::class)
        ->set('filterDomain', (string) $peinture->id)
        ->assertSee('Alice Martin')
        ->assertDontSee('Bob Dupont');
});

it('only shows main activities in the filter modal once a primary domain is selected', function () {
    $musique = Discipline::where('code', 'musique')->firstOrFail();
    Artist::factory()->published()->create(['discipline_main_id' => $musique->id]);

    Livewire::test(ArtistsIndex::class)
        ->assertSee('Pour afficher les activités principales, vous devez sélectionner un domaine.')
        ->assertDontSeeHtml('wire:model.live="filterActivities"')
        ->set('filterDomain', (string) $musique->id)
        ->assertDontSee('Pour afficher les activités principales, vous devez sélectionner un domaine.')
        ->assertSeeHtml('wire:model.live="filterActivities"');
});

it('sorts artists by name ascending by default', function () {
    Artist::factory()->published()->create(['artist_name' => 'Zoé Bernard']);
    Artist::factory()->published()->create(['artist_name' => 'Alice Martin']);

    Livewire::test(ArtistsIndex::class)
        ->assertSet('sort', 'name');
});

it('card links point to artist profile', function () {
    $artist = Artist::factory()->published()->create();

    Livewire::test(ArtistsIndex::class)
        ->assertSee(route('public.artist.show', $artist));
});

it('shows empty state when no artists match the search', function () {
    Artist::factory()->published()->create(['artist_name' => 'Alice Martin']);

    Livewire::test(ArtistsIndex::class)
        ->set('search', 'zzznomatch')
        ->assertSee('Aucun résultat ne correspond à votre recherche');
});

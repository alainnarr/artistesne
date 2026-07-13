<?php

use App\Livewire\Public\Home;
use App\Models\Artist;
use Livewire\Livewire;

it('responds with 200 on /', function () {
    $this->get('/')->assertOk()->assertSeeText('Annuaire des artistes');
});

it('shows the published artists with a count', function () {
    Artist::factory()->count(3)->published()->create();
    Artist::factory()->create();

    Livewire::test(Home::class)
        ->assertSee('Découvrir les artistes')
        ->assertSee('3 artistes référencé·es');
});

it('search bar on home is a native form targeting /artistes', function () {
    $this->get('/')->assertOk()->assertSee(route('public.artists.index'));
});

it('reveals more artists when "Afficher plus" is clicked', function () {
    Artist::factory()->count(12)->published()->create();

    Livewire::test(Home::class)
        ->assertSet('perPage', 9)
        ->call('showMore')
        ->assertSet('perPage', 18);
});

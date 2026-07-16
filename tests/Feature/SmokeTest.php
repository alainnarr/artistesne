<?php

use App\Database\Models\Artist;

it('public site key pages load without 5xx errors', function (string $route) {
    $url = match ($route) {
        'public.home' => route('public.home'),
        'public.about' => route('public.about'),
        'public.contact' => route('public.contact'),
        'public.artist-registration' => route('public.artist-registration'),
        'artist.login' => route('artist.login'),
        'public.artists.index' => route('public.artists.index'),
    };

    $response = $this->get($url);

    expect($response->getStatusCode())->toBeLessThan(500);
})->with([
    'public.home',
    'public.about',
    'public.contact',
    'public.artist-registration',
    'artist.login',
    'public.artists.index',
]);

it('artist profile page loads for a published artist', function () {
    $artist = Artist::factory()->published()->create();

    $this->get(route('public.artist.show', $artist))
        ->assertOk()
        ->assertSee($artist->artist_name);
});

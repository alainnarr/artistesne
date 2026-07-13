<?php

use App\Models\Artist;

it('public site key pages load without 5xx errors', function (string $route) {
    $url = match ($route) {
        'home' => route('home'),
        'about' => route('about'),
        'contact' => route('contact'),
        'artist.register' => route('artist.register'),
        'artist.login' => route('artist.login'),
        'public.artists.index' => route('public.artists.index'),
    };

    $response = $this->get($url);

    expect($response->getStatusCode())->toBeLessThan(500);
})->with([
    'home',
    'about',
    'contact',
    'artist.register',
    'artist.login',
    'public.artists.index',
]);

it('artist profile page loads for a published artist', function () {
    $artist = Artist::factory()->published()->create();

    $this->get(route('public.artist.show', $artist))
        ->assertOk()
        ->assertSee($artist->name);
});

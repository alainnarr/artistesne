<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('renders the DS component gallery without errors', function () {
    get('/dev/composants')
        ->assertOk()
        ->assertSee('Galerie de composants')
        ->assertSee('Buttons')
        ->assertSee('Card Artist')
        ->assertSee('Modal');
});

it('registers the dev.gallery named route', function () {
    expect(Route::has('dev.gallery'))->toBeTrue();
});

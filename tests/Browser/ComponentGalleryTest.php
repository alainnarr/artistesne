<?php

declare(strict_types=1);

it('renders the DS component gallery in a real browser without JS errors', function () {
    $page = visit('/dev/composants');

    $page
        ->assertNoJavaScriptErrors()
        ->assertSee('Galerie de composants')
        ->assertSee('Buttons')
        ->assertSee('Card Artist')
        ->assertSee('Modal');
});

it('opens the cookies banner and accepts it', function () {
    $page = visit('/dev/composants');

    $page
        ->assertSee('Nous utilisons des cookies')
        ->click('Accepter')
        ->assertNoJavaScriptErrors();
});

it('toggles an accordion section on click', function () {
    $page = visit('/dev/composants');

    // "Sous domaine" is a top-level accordion in the gallery (outside the modal).
    $page
        ->click('Sous domaine')
        ->assertNoJavaScriptErrors();
});

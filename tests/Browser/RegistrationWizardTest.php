<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Wizard de demande de référencement — UX navigateur (Playwright)
|--------------------------------------------------------------------------
|
| Le parcours complet (soumission, validations exhaustives) est couvert au
| niveau Livewire. Ici on valide l'expérience réelle : rendu, blocage de
| l'étape 1 avec message d'erreur visible, et rendu mobile.
|
*/

it('shows a validation message and stays on step 1 when advancing with empty fields', function () {
    visit('/devenir-artiste')
        ->assertSee('Étape suivante')
        ->click('Étape suivante')
        ->assertSee('Ce champ est obligatoire.')
        ->assertSee('Étape suivante')          // toujours sur l'étape 1
        ->assertNoJavaScriptErrors();
});

it('renders the wizard correctly on a mobile viewport', function () {
    visit('/devenir-artiste')
        ->on()->mobile()
        ->assertNoJavaScriptErrors()
        ->assertSee('Étape suivante');
});

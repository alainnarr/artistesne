<?php

use App\Livewire\Public\Contact;
use Livewire\Livewire;

it('renders the public contact page', function () {
    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('Nous')
        ->assertSee('contacter')
        ->assertSee('Formulaire de contact');
});

it('shows a confirmation after submitting the contact form', function () {
    Livewire::test(Contact::class)
        ->set('last_name', 'Dupont')
        ->set('first_name', 'Marie')
        ->set('email', 'marie@example.com')
        ->set('subject', 'Question')
        ->set('message', 'Bonjour')
        ->call('submit')
        ->assertSet('submitted', true)
        ->assertSee('Votre message a bien été envoyé');
});

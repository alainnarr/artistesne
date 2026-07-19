<?php

declare(strict_types=1);

use App\Database\Models\User;
use App\Livewire\Public\Contact;
use App\Livewire\Public\RequestModification;
use App\Notifications\ContactMessageNotification;
use App\Notifications\ModificationRequestNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

/*
|--------------------------------------------------------------------------
| Formulaires publics : validation + acheminement vers les administrateurs
|--------------------------------------------------------------------------
|
| Contact et RequestModification étaient « visual-only ». Ils valident
| désormais leurs entrées et notifient les administrateurs.
|
*/

// --- Formulaire de contact ---------------------------------------------------

it('validates the contact form before submitting', function () {
    Livewire::test(Contact::class)
        ->set('last_name', '')
        ->set('first_name', '')
        ->set('email', 'not-an-email')
        ->set('subject', '')
        ->set('message', '')
        ->call('submit')
        ->assertHasErrors([
            'last_name' => 'required',
            'first_name' => 'required',
            'email' => 'email',
            'subject' => 'required',
            'message' => 'required',
        ])
        ->assertSet('submitted', false);
});

it('notifies administrators when the contact form is submitted', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    User::factory()->artist()->create(); // must not be notified

    Livewire::test(Contact::class)
        ->set('last_name', 'Dupont')
        ->set('first_name', 'Marie')
        ->set('email', 'marie@example.com')
        ->set('subject', 'Question sur mon profil')
        ->set('message', 'Bonjour, je souhaite une information.')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    Notification::assertSentTo($admin, ContactMessageNotification::class);
    Notification::assertCount(1);
});

// --- Demande de modification -------------------------------------------------

it('validates the modification request form', function () {
    Livewire::test(RequestModification::class)
        ->set('email', 'invalid')
        ->set('request_type', 'update')
        ->call('submit')
        ->assertHasErrors(['email' => 'email'])
        ->assertSet('submitted', false);
});

it('rejects an unknown modification request type', function () {
    Livewire::test(RequestModification::class)
        ->set('email', 'artist@example.com')
        ->set('request_type', 'hack')
        ->call('submit')
        ->assertHasErrors(['request_type'])
        ->assertSet('submitted', false);
});

it('notifies administrators for an update request', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();

    Livewire::test(RequestModification::class)
        ->set('email', 'artist@example.com')
        ->set('request_type', 'update')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    Notification::assertSentTo($admin, ModificationRequestNotification::class);
});

it('notifies administrators for a deletion request', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();

    Livewire::test(RequestModification::class)
        ->set('email', 'artist@example.com')
        ->set('request_type', 'delete')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    Notification::assertSentTo(
        $admin,
        ModificationRequestNotification::class,
        fn (ModificationRequestNotification $n) => $n->requestType === 'delete',
    );
});

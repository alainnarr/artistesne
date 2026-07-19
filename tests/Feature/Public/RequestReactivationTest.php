<?php

declare(strict_types=1);

use App\Database\Models\User;
use App\Livewire\Public\RequestReactivation;
use App\Notifications\ReactivationRequestNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

it('renders the reactivation request page', function () {
    $this->get(route('public.reactivation-request'))->assertOk();
});

it('validates the reactivation request form', function () {
    Livewire::test(RequestReactivation::class)
        ->set('email', 'invalid')
        ->call('submit')
        ->assertHasErrors(['email' => 'email'])
        ->assertSet('submitted', false);
});

it('notifies administrators when a reactivation is requested', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();

    Livewire::test(RequestReactivation::class)
        ->set('email', 'artist@example.com')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    Notification::assertSentTo($admin, ReactivationRequestNotification::class);
});

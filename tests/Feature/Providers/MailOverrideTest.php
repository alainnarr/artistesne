<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Email;

it('redirects outgoing mail to every address in a comma-separated MAIL_TO_ADDRESS', function () {
    config(['mail.to' => [
        'addresses' => ['dev1@example.com', 'dev2@example.com'],
        'name' => 'Dev Catch-All',
    ]]);

    // Re-run boot so the listener picks up the config set above (normally bound once at app boot).
    (new AppServiceProvider(app()))->boot();

    $email = (new Email)
        ->from('app@example.com')
        ->to('someone-else@example.com')
        ->subject('Test')
        ->html('<p>Body</p>');

    event(new MessageSending($email));

    $toAddresses = collect($email->getTo())->map(fn ($address) => $address->getAddress())->all();

    expect($toAddresses)->toBe(['dev1@example.com', 'dev2@example.com']);
});

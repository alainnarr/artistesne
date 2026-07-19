<?php

use App\Database\Models\User;
use App\Notifications\MagicLinkNotification;
use App\Notifications\RegistrationApprovedNotification;
use Illuminate\Mail\Markdown;

it('renders notification emails with the Artistes.ne branded theme (logos, colors)', function () {
    $user = User::factory()->artist()->create(['name' => 'Jeanne Dupont']);

    $mail = (new MagicLinkNotification)->toMail($user);

    $html = (string) app(Markdown::class)->render($mail->markdown, $mail->data());

    expect($html)
        ->toContain('img/mail/artistes-ne-white.svg')
        ->toContain('img/mail/ne-dark.svg')
        ->toContain('#2e3d3c')
        ->toContain('#bfeceb');
});

it('renders the registration approved email through the same branded theme', function () {
    $user = new class
    {
        public string $name = 'Jeanne Dupont';

        public function getKey(): int
        {
            return 42;
        }
    };

    $mail = (new RegistrationApprovedNotification('Jeanne Dupont'))->toMail($user);

    $html = (string) app(Markdown::class)->render($mail->markdown, $mail->data());

    expect($html)
        ->toContain('img/mail/artistes-ne-white.svg')
        ->toContain('img/mail/ne-dark.svg')
        ->toContain('Jeanne Dupont')
        ->toContain('Créer mon profil');
});

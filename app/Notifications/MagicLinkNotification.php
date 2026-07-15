<?php

namespace App\Notifications;

use Carbon\CarbonInterval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class MagicLinkNotification extends Notification implements ShouldQueue
{
    use Queueable;

    // Magic links are valid for one week (see SPECS Feature 2.3).
    public function __construct(
        public int $expiresInMinutes = 60 * 24 * 7,
        public bool $afterApproval = false,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Single-use: mint a fresh token and persist it on the user, so this
        // specific link is invalidated the moment it's consumed (or a newer
        // link is requested) even though its signature stays valid until expiry.
        $token = Str::random(40);
        $notifiable->forceFill(['magic_link_token' => $token])->save();

        $url = URL::temporarySignedRoute(
            'artist.magic-link.consume',
            now()->addMinutes($this->expiresInMinutes),
            ['user' => $notifiable->getKey(), 'token' => $token],
        );

        $validity = CarbonInterval::minutes($this->expiresInMinutes)->cascade()->locale('fr')->forHumans();

        $mail = (new MailMessage)
            ->subject('Votre lien de connexion — Inventaire des artistes')
            ->greeting('Bonjour '.$notifiable->name.',');

        if ($this->afterApproval) {
            $mail->line('Votre demande de référencement a été approuvée.');
        }

        return $mail
            ->line('Cliquez sur le bouton ci-dessous pour vous connecter à votre espace artiste.')
            ->action('Me connecter', $url)
            ->line('Ce lien est valable '.$validity.'. Si vous n\'êtes pas à l\'origine de cette demande, ignorez ce message.');
    }
}

<?php

namespace App\Notifications;

use Carbon\CarbonInterval;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class MagicLinkNotification extends Notification
{
    // Magic links are valid for one week (see SPECS Feature 2.3).
    public function __construct(public int $expiresInMinutes = 60 * 24 * 7) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'artist.magic-link.consume',
            now()->addMinutes($this->expiresInMinutes),
            ['user' => $notifiable->getKey()],
        );

        $validity = CarbonInterval::minutes($this->expiresInMinutes)->cascade()->locale('fr')->forHumans();

        return (new MailMessage)
            ->subject('Votre lien de connexion — Inventaire des artistes')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Cliquez sur le bouton ci-dessous pour vous connecter à votre espace artiste.')
            ->action('Me connecter', $url)
            ->line('Ce lien est valable '.$validity.'. Si vous n\'êtes pas à l\'origine de cette demande, ignorez ce message.');
    }
}

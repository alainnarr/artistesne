<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationApprovedNotification extends Notification
{
    public function __construct(public string $artistName) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre demande de référencement a été approuvée — Artistes.ne')
            ->greeting('Bonjour '.$this->artistName.',')
            ->line('Nous avons le plaisir de vous informer que votre demande de référencement sur la plateforme Artistes.ne a été approuvée par l\'équipe du SCNE.')
            ->line('Vous allez recevoir dans quelques instants un e-mail contenant votre lien de connexion pour accéder à votre espace artiste et compléter votre profil.')
            ->line('Bienvenue dans l\'inventaire des artistes neuchâtelois·es !');
    }
}

<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProfileReactivatedNotification extends Notification
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
            ->subject('Votre profil a été réactivé — Artistes.ne')
            ->greeting('Bonjour '.$this->artistName.',')
            ->line('Votre profil est à nouveau visible dans l\'inventaire des artistes neuchâtelois·es.')
            ->line('Si vos informations ont évolué, vous pouvez les mettre à jour à tout moment depuis votre espace artiste.')
            ->salutation('L\'équipe du SCNE');
    }
}

<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent when an artist's profile is automatically disabled after failing to confirm within 4 weeks.
 */
class ProfileAutoDisabledNotification extends Notification
{
    public function __construct(public string $reactivateUrl) {}

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
            ->subject('Votre profil Artistes.ne a été désactivé')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Faute de confirmation dans le délai imparti, votre profil sur la plateforme Artistes.ne a été automatiquement désactivé et n\'est plus visible du public.')
            ->line('Vous pouvez le réactiver à tout moment en cliquant sur le bouton ci-dessous.')
            ->action('Réactiver mon profil', $this->reactivateUrl)
            ->line('Si vous avez des questions, n\'hésitez pas à nous contacter.');
    }
}

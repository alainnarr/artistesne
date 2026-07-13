<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ArtistRegistrationAccessNotification extends Notification
{
    public function __construct(
        public string $email,
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
        return (new MailMessage)
            ->subject('Démarrez votre demande de référencement')
            ->greeting('Bonjour,')
            ->line('Aucun compte artiste n\'est associé à cette adresse e-mail.')
            ->line('Vous pouvez remplir le formulaire de demande de référencement dès maintenant.')
            ->action('Accéder au formulaire', route('artist.register'));
    }
}

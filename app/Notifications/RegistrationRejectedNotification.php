<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationRejectedNotification extends Notification
{
    public function __construct(
        public string $artistName,
        public ?string $notes = null,
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
        $message = (new MailMessage)
            ->subject('Suite à votre demande de référencement — Artistes.ne')
            ->greeting('Bonjour '.$this->artistName.',')
            ->line('Après examen de votre demande de référencement sur la plateforme Artistes.ne, nous ne sommes malheureusement pas en mesure de donner suite à votre dossier dans l\'état actuel.');

        if (filled($this->notes)) {
            $message->line('**Motif communiqué par l\'équipe du SCNE :**')
                ->line($this->notes);
        }

        return $message
            ->line('Si vous pensez que cette décision est erronée ou si votre situation a évolué, n\'hésitez pas à nous contacter directement.');
    }
}

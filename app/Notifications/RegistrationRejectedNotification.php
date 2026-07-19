<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

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
            ->line('Nous avons bien examiné votre demande de référencement avec attention et nous vous remercions de l\'intérêt que vous portez à l\'annuaire.')
            ->line('Après examen, nous ne sommes malheureusement pas en mesure de donner une suite favorable à votre demande.');

        if (filled($this->notes)) {
            $message->line('**Motif communiqué par l\'équipe du SCNE :**')
                ->line($this->notes);
        }

        return $message->line('Si vous souhaitez en savoir plus ou échanger à ce sujet, n\'hésitez pas à nous contacter à l\'adresse suivante : service.culture@ne.ch.');
    }
}

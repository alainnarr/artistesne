<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReactivationRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $senderEmail,
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
            ->subject('[Demande de réactivation] Artistes.ne')
            ->replyTo($this->senderEmail)
            ->greeting('Nouvelle demande de réactivation de profil')
            ->line('**E-mail du profil concerné :** '.$this->senderEmail)
            ->line('Merci de traiter cette demande depuis l\'administration.');
    }
}

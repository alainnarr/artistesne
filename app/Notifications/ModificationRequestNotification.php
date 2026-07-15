<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ModificationRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $senderEmail,
        public string $requestType,
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
        $typeLabel = match ($this->requestType) {
            'delete' => 'Suppression du compte',
            default => 'Mise à jour du profil',
        };

        return (new MailMessage)
            ->subject('[Demande de modification] '.$typeLabel.' — Artistes.ne')
            ->replyTo($this->senderEmail)
            ->greeting('Nouvelle demande de modification')
            ->line('**Type de demande :** '.$typeLabel)
            ->line('**E-mail du profil concerné :** '.$this->senderEmail)
            ->line('Merci de traiter cette demande depuis l\'administration.');
    }
}

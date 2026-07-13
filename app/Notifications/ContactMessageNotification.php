<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $lastName,
        public string $firstName,
        public string $senderEmail,
        public string $messageSubject,
        public string $body,
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
            ->subject('[Contact] '.$this->messageSubject.' — Artistes.ne')
            ->replyTo($this->senderEmail, $this->firstName.' '.$this->lastName)
            ->greeting('Nouveau message de contact')
            ->line('**Nom :** '.$this->firstName.' '.$this->lastName)
            ->line('**E-mail :** '.$this->senderEmail)
            ->line('**Sujet :** '.$this->messageSubject)
            ->line('**Message :**')
            ->line($this->body);
    }
}

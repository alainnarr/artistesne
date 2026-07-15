<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Database\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRegistrationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Registration $registration) {}

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
            ->subject('[Nouvelle demande] '.$this->registration->artist_name.' — Artistes.ne')
            ->greeting('Bonjour,')
            ->line('**'.$this->registration->artist_name.'** ('.$this->registration->email.') vient de soumettre une demande de référencement.')
            ->line('Connectez-vous à l\'administration pour traiter cette demande.');
    }
}

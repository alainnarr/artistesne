<?php

namespace App\Notifications;

use App\Models\ArtistRegistrationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRegistrationRequestNotification extends Notification
{
    use Queueable;

    public function __construct(public ArtistRegistrationRequest $request) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('filament.admin.resources.artist-registration-requests.edit', $this->request);

        return (new MailMessage)
            ->subject('[Nouvelle demande] '.$this->request->artist_name.' — Artistes.ne')
            ->greeting('Bonjour,')
            ->line('**'.$this->request->artist_name.'** ('.$this->request->email.') vient de soumettre une demande de référencement.')
            ->action('Examiner la demande', $url)
            ->line('Connectez-vous à l\'administration pour traiter cette demande.');
    }
}

<?php

namespace App\Notifications;

use App\Models\ArtistChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChangeRequestSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public ArtistChangeRequest $changeRequest) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $artist = $this->changeRequest->artist;
        $url = route('filament.admin.resources.artist-change-requests.edit', $this->changeRequest);

        return (new MailMessage)
            ->subject('Nouvelle demande de modification — '.$artist->name)
            ->greeting('Bonjour,')
            ->line('L\'artiste **'.$artist->name.'** a soumis une demande de modification de sa page.')
            ->action('Examiner la demande', $url)
            ->line('Connectez-vous à l\'administration pour approuver ou refuser cette modification.');
    }
}

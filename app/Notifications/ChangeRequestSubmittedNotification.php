<?php

namespace App\Notifications;

use App\Database\Models\ArtistChangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChangeRequestSubmittedNotification extends Notification implements ShouldQueue
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
        $artistName = $artist !== null ? $artist->artist_name : 'Inconnu';
        $url = route('filament.admin.resources.artist-change-requests.edit', $this->changeRequest);

        return (new MailMessage)
            ->subject('Nouvelle demande de modification — '.$artistName)
            ->greeting('Bonjour,')
            ->line('L\'artiste **'.$artistName.'** a soumis une demande de modification de sa page.')
            ->line('Connectez-vous à l\'administration pour approuver ou refuser cette modification.')
            ->action('Examiner la demande', $url);
    }
}

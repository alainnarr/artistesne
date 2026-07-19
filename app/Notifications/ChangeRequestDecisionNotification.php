<?php

namespace App\Notifications;

use App\Database\Models\ArtistChangeRequest;
use App\Enums\ArtistChangeRequestStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChangeRequestDecisionNotification extends Notification implements ShouldQueue
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
        $status = $this->changeRequest->status;
        $notes = $this->changeRequest->review_notes;

        $subject = match ($status) {
            ArtistChangeRequestStatus::APPROVED => 'Votre modification a été approuvée',
            ArtistChangeRequestStatus::REJECTED => 'Votre demande de modification a été refusée',
            ArtistChangeRequestStatus::CHANGES_REQUESTED => 'Des ajustements sont demandés pour votre modification',
            default => 'Mise à jour de votre demande de modification',
        };

        $intro = match ($status) {
            ArtistChangeRequestStatus::APPROVED => 'Bonne nouvelle ! Votre demande de modification a été **approuvée** et votre page a été mise à jour.',
            ArtistChangeRequestStatus::REJECTED => 'Votre demande de modification a été **refusée**.',
            ArtistChangeRequestStatus::CHANGES_REQUESTED => 'Des ajustements sont nécessaires avant de pouvoir approuver votre modification.',
            default => 'Votre demande de modification a été traitée.',
        };

        $message = (new MailMessage)
            ->subject($subject.' — Inventaire des artistes')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line($intro);

        if ($notes) {
            $message->line('**Message de l\'administrateur :** '.$notes);
        }

        if ($status === ArtistChangeRequestStatus::CHANGES_REQUESTED) {
            $message->action('Modifier ma page', route('artist.profile-edit'));
        } elseif ($status === ArtistChangeRequestStatus::APPROVED) {
            $message->action('Voir ma page', route('public.artist.show', $this->changeRequest->artist));
        }

        return $message;
    }
}

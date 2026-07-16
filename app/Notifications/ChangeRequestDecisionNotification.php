<?php

namespace App\Notifications;

use App\Database\Models\ArtistChangeRequest;
use App\Enums\ApprovalStatus;
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
            ApprovalStatus::Approved => 'Votre modification a été approuvée',
            ApprovalStatus::Rejected => 'Votre demande de modification a été refusée',
            ApprovalStatus::ChangesRequested => 'Des ajustements sont demandés pour votre modification',
            default => 'Mise à jour de votre demande de modification',
        };

        $intro = match ($status) {
            ApprovalStatus::Approved => 'Bonne nouvelle ! Votre demande de modification a été **approuvée** et votre page a été mise à jour.',
            ApprovalStatus::Rejected => 'Votre demande de modification a été **refusée**.',
            ApprovalStatus::ChangesRequested => 'Des ajustements sont nécessaires avant de pouvoir approuver votre modification.',
            default => 'Votre demande de modification a été traitée.',
        };

        $message = (new MailMessage)
            ->subject($subject.' — Inventaire des artistes')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line($intro);

        if ($notes) {
            $message->line('**Message de l\'administrateur :** '.$notes);
        }

        if ($status === ApprovalStatus::ChangesRequested) {
            $message->action('Modifier ma page', route('artist.profile-edit'));
        } elseif ($status === ApprovalStatus::Approved) {
            $message->action('Voir ma page', route('public.artist.show', $this->changeRequest->artist));
        }

        return $message;
    }
}

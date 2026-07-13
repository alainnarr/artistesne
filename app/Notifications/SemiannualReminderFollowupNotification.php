<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent J-7 before auto-disable as a final reminder.
 */
class SemiannualReminderFollowupNotification extends Notification
{
    public function __construct(
        public string $confirmUrl,
        public string $updateUrl,
        public int $daysRemaining = 7,
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
            ->subject('Rappel urgent — votre profil Artistes.ne sera désactivé dans '.$this->daysRemaining.' jours')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Vous avez reçu un e-mail il y a quelques semaines vous demandant de confirmer que votre profil Artistes.ne est à jour.')
            ->line('**Il vous reste '.$this->daysRemaining.' jours** avant que votre profil ne soit automatiquement désactivé.')
            ->action('Oui, mon profil est à jour', $this->confirmUrl)
            ->action('Mettre à jour mon profil', $this->updateUrl)
            ->line('Après désactivation, votre profil ne sera plus visible du public. Vous pourrez le réactiver à tout moment depuis l\'espace artiste.');
    }
}

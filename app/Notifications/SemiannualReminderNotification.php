<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent every 6 months to published artists asking them to confirm their profile is still up-to-date.
 * Contains two signed action links:
 *   - "Mon profil est à jour"       → marks confirmed, no edit needed
 *   - "Mettre à jour mon profil"    → marks confirmed + opens edit form
 */
class SemiannualReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $confirmUrl,
        public string $updateUrl,
        public int $daysUntilDisable = 28,
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
            ->subject('Confirmez que votre profil Artistes.ne est à jour')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Il est temps de vérifier que votre profil sur la plateforme Artistes.ne est encore à jour.')
            ->line('Si vous ne prenez aucune action dans **'.$this->daysUntilDisable.' jours**, votre profil sera automatiquement désactivé et ne sera plus visible du public.')
            ->action('Oui, mon profil est à jour', $this->confirmUrl)
            ->line('Si vous souhaitez apporter des modifications avant de confirmer, utilisez plutôt le lien suivant : '.$this->updateUrl)
            ->line('Merci de maintenir votre profil à jour pour garantir la qualité de l\'annuaire.');
    }
}

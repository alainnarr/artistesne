<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class RegistrationApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $artistName) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $magicLinkUrl = URL::temporarySignedRoute(
            'artist.magic-link.consume',
            now()->addWeek(),
            ['user' => $notifiable->getKey()],
        );

        return (new MailMessage)
            ->subject('Votre demande de référencement a été approuvée — Artistes.ne')
            ->greeting('Bonjour '.$this->artistName.',')
            ->line('Nous avons bien examiné votre demande de référencement et nous avons le plaisir de vous informer qu\'elle a été acceptée.')
            ->line('Vous pouvez désormais créer votre profil sur Artistes.ne en cliquant sur le lien ci-dessous. Il vous permettra de compléter votre fiche : photo, texte de présentation, liens vers vos espaces personnels et collaborations, mots-clés.')
            ->action('Créer mon profil', $magicLinkUrl)
            ->line('Une fois votre profil complété, il sera visible sur l\'annuaire.')
            ->line('Bienvenue parmi les artistes de l\'annuaire neuchâtelois. L\'équipe du SCNE.');
    }
}

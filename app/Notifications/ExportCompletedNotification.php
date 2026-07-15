<?php

namespace App\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Emails the user who triggered a Filament export once it has finished,
 * since the admin panel does not expose a bell/database-notifications UI
 * by default and queued export completions would otherwise go unnoticed.
 */
class ExportCompletedNotification extends Notification implements ShouldQueue
{
    /**
     * @param  array<int, array{label: string, url: string}>  $downloadLinks
     */
    public function __construct(
        public string $title,
        public string $body,
        public array $downloadLinks,
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
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting($this->title)
            ->line($this->body);

        foreach ($this->downloadLinks as $link) {
            $mail->action($link['label'], $link['url']);
        }

        return $mail;
    }
}

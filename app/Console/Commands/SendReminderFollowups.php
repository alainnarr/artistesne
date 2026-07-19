<?php

namespace App\Console\Commands;

use App\Database\Models\Artist;
use App\Enums\ArtistStatus;
use App\Notifications\SemiannualReminderFollowupNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;

class SendReminderFollowups extends Command
{
    protected $signature = 'artist:send-reminder-followups {--dry-run}';

    protected $description = 'Send J-7 followup reminders to artists who have not confirmed after 3 weeks.';

    public function handle(): int
    {
        // Artists who received initial reminder 21+ days ago but haven't confirmed.
        $artists = Artist::query()
            ->where('enum_status', ArtistStatus::PUBLISHED->value)
            ->whereNotNull('reminder_sent_at')
            ->where('reminder_sent_at', '<', now()->subDays(21))
            ->whereNull('last_confirmed_at')
            ->with('user')
            ->get();

        if ($artists->isEmpty()) {
            $this->info('No followup reminders to send.');

            return self::SUCCESS;
        }

        $this->info("Sending followups to {$artists->count()} artist(s).");

        if ($this->option('dry-run')) {
            $artists->each(fn ($a) => $this->line("  - {$a->artist_name} ({$a->email})"));

            return self::SUCCESS;
        }

        foreach ($artists as $artist) {
            if (! $artist->user || ! $artist->confirmation_token) {
                continue;
            }
            $confirmUrl = URL::signedRoute('artist.confirm-profile', ['token' => $artist->confirmation_token]);
            $updateUrl = URL::signedRoute('artist.confirm-update', ['token' => $artist->confirmation_token]);
            $artist->user->notify(new SemiannualReminderFollowupNotification($confirmUrl, $updateUrl, 7));
        }

        $this->info('Followup reminders sent.');

        return self::SUCCESS;
    }
}

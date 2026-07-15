<?php

namespace App\Console\Commands;

use App\Database\Models\Artist;
use App\Enums\ArtistStatus;
use App\Notifications\ProfileAutoDisabledNotification;
use Illuminate\Console\Command;

class DisableInactiveArtists extends Command
{
    protected $signature = 'artist:disable-inactive {--dry-run}';

    protected $description = 'Disable published artists who did not confirm within 4 weeks of reminder.';

    public function handle(): int
    {
        // Artists who received a reminder 28+ days ago and never confirmed.
        $artists = Artist::query()
            ->where('enum_status', ArtistStatus::Published->value)
            ->whereNotNull('reminder_sent_at')
            ->where('reminder_sent_at', '<', now()->subDays(28))
            ->whereNull('last_confirmed_at')
            ->with('user')
            ->get();

        if ($artists->isEmpty()) {
            $this->info('No artists to disable.');

            return self::SUCCESS;
        }

        $this->info("Disabling {$artists->count()} artist(s).");

        if ($this->option('dry-run')) {
            $artists->each(fn ($a) => $this->line("  - {$a->artist_name}"));

            return self::SUCCESS;
        }

        foreach ($artists as $artist) {
            $artist->update([
                'enum_status' => ArtistStatus::Draft->value,
                'reminder_sent_at' => null,
                'confirmation_token' => null,
            ]);

            // Notify user.
            if ($artist->user) {
                $reactivateUrl = route('artist.login');
                $artist->user->notify(new ProfileAutoDisabledNotification($reactivateUrl));
            }
        }

        $this->info('Artists disabled and notified.');

        return self::SUCCESS;
    }
}

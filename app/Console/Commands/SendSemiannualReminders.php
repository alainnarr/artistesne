<?php

namespace App\Console\Commands;

use App\Database\Models\Artist;
use App\Enums\ArtistStatus;
use App\Notifications\SemiannualReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class SendSemiannualReminders extends Command
{
    protected $signature = 'artist:send-reminders {--dry-run : List artists without sending}';

    protected $description = 'Send semiannual profile confirmation reminders to published artists.';

    public function handle(): int
    {
        $cutoff = now()->subMonths(6);

        $artists = Artist::query()
            ->where('enum_status', ArtistStatus::Published->value)
            ->where(function ($q) use ($cutoff) {
                $q->whereNull('last_confirmed_at')
                    ->orWhere('last_confirmed_at', '<', $cutoff);
            })
            ->whereNull('reminder_sent_at')
            ->with('user')
            ->get();

        if ($artists->isEmpty()) {
            $this->info('No artists require a reminder.');

            return self::SUCCESS;
        }

        $this->info("Found {$artists->count()} artist(s) to notify.");

        if ($this->option('dry-run')) {
            $this->table(['ID', 'Name', 'Last confirmed'], $artists->map(fn ($a) => [
                $a->id, $a->artist_name, $a->last_confirmed_at?->toDateString() ?? 'never',
            ]));

            return self::SUCCESS;
        }

        foreach ($artists as $artist) {
            if (! $artist->user) {
                continue;
            }
            $token = Str::random(64);
            $artist->update(['confirmation_token' => $token, 'reminder_sent_at' => now()]);
            $confirmUrl = URL::signedRoute('artist.confirm-profile', ['token' => $token]);
            $updateUrl = URL::signedRoute('artist.confirm-update', ['token' => $token]);
            $artist->user->notify(new SemiannualReminderNotification($confirmUrl, $updateUrl));
        }

        $this->info('Reminders sent.');

        return self::SUCCESS;
    }
}

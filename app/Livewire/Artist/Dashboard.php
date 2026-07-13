<?php

namespace App\Livewire\Artist;

use App\Enums\ArtistStatus;
use App\Models\User;
use App\Notifications\ProfileReactivatedNotification;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.artist')]
class Dashboard extends Component
{
    public function reactivate(): void
    {
        /** @var User $user */
        $user = auth()->user();
        $artist = $user->artist;

        abort_unless($artist !== null, 403);
        abort_if($artist->isPublished(), 403);

        $artist->update([
            'status' => ArtistStatus::Published,
            'last_confirmed_at' => now(),
        ]);

        $user->notify(new ProfileReactivatedNotification($artist->name));

        session()->flash('status', 'Votre profil a été réactivé et est à nouveau visible dans l\'annuaire.');
    }

    public function render(): View
    {
        /** @var User|null $user */
        $user = auth()->user();
        $artist = $user?->artist;

        return view('livewire.artist.dashboard', [
            'artist' => $artist,
            'pendingChange' => $artist?->pendingChangeRequest(),
        ]);
    }
}

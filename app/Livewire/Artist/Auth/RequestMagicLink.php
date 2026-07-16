<?php

namespace App\Livewire\Artist\Auth;

use App\Database\Models\User;
use App\Enums\UserRole;
use App\Notifications\ArtistRegistrationAccessNotification;
use App\Notifications\MagicLinkNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.public')]
class RequestMagicLink extends Component
{
    #[Validate('required|email|max:255')]
    public string $email = '';

    public bool $sent = false;

    public function send(): void
    {
        $this->validate();

        $user = User::query()
            ->where('email', $this->email)
            ->where('role', UserRole::Artist)
            ->first();

        if (! $user) {
            Notification::route('mail', $this->email)
                ->notify(new ArtistRegistrationAccessNotification($this->email));

            $this->sent = true;

            return;
        }

        if ($this->isThrottled($user)) {
            $this->sent = true;

            return;
        }

        $user->forceFill(['last_magic_link_sent_at' => now()])->save();
        $user->notify(new MagicLinkNotification);
        $this->sent = true;
    }

    private function isThrottled(User $user): bool
    {
        return $user->last_magic_link_sent_at?->gt(now()->subMinute()) ?? false;
    }

    public function render(): View
    {
        return view('livewire.artist.auth.request-magic-link');
    }
}

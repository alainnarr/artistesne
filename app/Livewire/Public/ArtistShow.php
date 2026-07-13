<?php

namespace App\Livewire\Public;

use App\Models\Artist;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public')]
class ArtistShow extends Component
{
    public Artist $artist;

    public function mount(Artist $artist): void
    {
        abort_unless($artist->isPublished(), 404);
        $this->artist = $artist;
    }

    public function render(): View
    {
        return view('livewire.public.artist-show');
    }
}

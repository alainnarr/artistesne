<?php

namespace App\Livewire\Public;

use App\Database\Models\Activity;
use App\Database\Models\Artist;
use App\Database\Models\Discipline;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public')]
class Home extends Component
{
    public string $sort = 'random';

    /** Base page size; becomes 12 on very large screens (>=1920px), 9 otherwise. */
    public int $pageSize = 9;

    public int $perPage = 9;

    public string $homeSearch = '';

    /**
     * Called once from the browser with the viewport width so the default page
     * size follows the spec (9 cards, 12 from 1920px wide).
     */
    public function initializePageSize(int $viewportWidth): void
    {
        $this->pageSize = $viewportWidth >= 1920 ? 12 : 9;
        $this->perPage = max($this->perPage, $this->pageSize);
    }

    public function showMore(): void
    {
        $this->perPage += $this->pageSize;
    }

    /**
     * @return array<string, array<int, string>> Grouped suggestions keyed by category label.
     *
     * Ordered by discovery priority (domains → activities → artist names),
     * matching the full listing page, so exploring artistic domains is
     * favored over searching for a specific artist by name.
     */
    #[Computed]
    public function suggestions(): array
    {
        $q = trim($this->homeSearch);
        if (strlen($q) < 3) {
            return [];
        }

        $groups = [];

        $domains = Discipline::where('label', 'like', "%{$q}%")
            ->orderBy('label')
            ->take(3)
            ->pluck('label')
            ->all();
        if (! empty($domains)) {
            $groups['Domaines'] = $domains;
        }

        $activities = Activity::where('label', 'like', "%{$q}%")
            ->distinct()
            ->orderBy('label')
            ->take(5)
            ->pluck('label')
            ->all();
        if (! empty($activities)) {
            $groups['Activités'] = $activities;
        }

        $artists = Artist::search($q)
            ->options(['attributesToSearchOn' => ['artist_name']])
            ->query(fn ($query) => $query->select('id', 'artist_name'))
            ->take(5)
            ->get()
            ->pluck('artist_name')
            ->all();
        if (! empty($artists)) {
            $groups['Artistes'] = $artists;
        }

        return $groups;
    }

    public function render(): View
    {
        $query = Artist::query()->published();

        $total = (clone $query)->count();

        $query = match ($this->sort) {
            'name' => $query->orderBy('artist_name'),
            default => $query->inRandomOrder(),
        };

        /** @var Collection<int, Artist> $artists */
        $artists = $query->limit($this->perPage)->get();

        return view('livewire.public.home', [
            'artists' => $artists,
            'total' => $total,
            'hasMore' => $total > $this->perPage,
            'suggestions' => $this->suggestions(),
        ]);
    }
}

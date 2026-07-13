<?php

namespace App\Livewire\Public;

use App\Models\Artist;
use App\Models\TaxonomyTerm;
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
     * Ordered by discovery priority (tags → domains → artist names), matching
     * the full listing page, so exploring artistic domains is favored over
     * searching for a specific artist by name.
     */
    #[Computed]
    public function suggestions(): array
    {
        $q = trim($this->homeSearch);
        if (strlen($q) < 3) {
            return [];
        }

        $groups = [];

        $keywords = TaxonomyTerm::query()
            ->where('type', 'keywords')
            ->where('name', 'like', "%{$q}%")
            ->orderBy('name')
            ->take(5)
            ->pluck('name')
            ->all();
        if (! empty($keywords)) {
            $groups['Mots-clés'] = $keywords;
        }

        $domains = TaxonomyTerm::query()
            ->where('type', 'domain')
            ->where('name', 'like', "%{$q}%")
            ->orderBy('position')
            ->take(3)
            ->pluck('name')
            ->all();
        if (! empty($domains)) {
            $groups['Domaines'] = $domains;
        }

        $activities = TaxonomyTerm::query()
            ->where('type', 'main_activities')
            ->where('name', 'like', "%{$q}%")
            ->distinct()
            ->orderBy('name')
            ->take(5)
            ->pluck('name')
            ->all();
        if (! empty($activities)) {
            $groups['Activités'] = $activities;
        }

        $artists = Artist::search($q)
            ->options(['attributesToSearchOn' => ['name']])
            ->query(fn ($query) => $query->select('id', 'name'))
            ->take(5)
            ->get()
            ->pluck('name')
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
            'name' => $query->orderBy('name'),
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

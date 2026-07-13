<?php

namespace App\Livewire\Public;

use App\Models\Artist;
use App\Models\TaxonomyTerm;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.public')]
class ArtistsIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    /** Tracks the current input value for live suggestions without triggering a filter. */
    public string $draftSearch = '';

    #[Url]
    public string $filterDomain = '';

    #[Url]
    public string $filterSecondaryDomain = '';

    #[Url]
    public string $filterLocality = '';

    /** @var array<int, string> */
    #[Url]
    public array $filterActivities = [];

    /** @var array<int, string> */
    #[Url]
    public array $filterSecondaryActivities = [];

    #[Url]
    public string $sort = 'name';

    public function mount(): void
    {
        $this->draftSearch = $this->search;
    }

    public function updating(string $name): void
    {
        if (str_starts_with($name, 'filter') || $name === 'sort') {
            $this->resetPage();
        }
    }

    public function updatedFilterDomain(): void
    {
        $this->filterActivities = [];
    }

    public function updatedFilterSecondaryDomain(): void
    {
        $this->filterSecondaryActivities = [];
    }

    /** Sync draftSearch when search is set via URL navigation. */
    public function updatedSearch(): void
    {
        $this->draftSearch = $this->search;
    }

    /** Apply the current draftSearch as the committed search and trigger filtering. */
    public function applySearch(): void
    {
        $this->search = $this->draftSearch;
        $this->resetPage();

        if ($this->search !== '' && $this->sort === 'name') {
            $this->sort = 'relevance';
        } elseif ($this->search === '' && $this->sort === 'relevance') {
            $this->sort = 'name';
        }
    }

    public function clearFilters(): void
    {
        $this->filterDomain = '';
        $this->filterSecondaryDomain = '';
        $this->filterLocality = '';
        $this->filterActivities = [];
        $this->filterSecondaryActivities = [];
        $this->resetPage();
    }

    // ── Computed ─────────────────────────────────────────────────────────────

    #[Computed]
    public function filterCount(): int
    {
        return ($this->filterDomain !== '' ? 1 : 0)
            + ($this->filterSecondaryDomain !== '' ? 1 : 0)
            + count($this->filterActivities)
            + count($this->filterSecondaryActivities)
            + ($this->filterLocality !== '' ? 1 : 0);
    }

    /**
     * @return array<string, array<int, string>> Grouped suggestions keyed by category label.
     *
     * Ordered by discovery priority (tags → domains → activities → artist
     * names) so exploration of artistic domains is favored over searching
     * for a specific artist by name.
     */
    #[Computed]
    public function suggestions(): array
    {
        $q = trim($this->draftSearch);
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

    #[Computed]
    public function suggestionResultCount(): ?int
    {
        $q = trim($this->draftSearch);
        if (strlen($q) < 3) {
            return null;
        }

        return Artist::search($q)->keys()->count();
    }

    // ── Render ────────────────────────────────────────────────────────────────

    /**
     * Published artists matching the current search and every active filter
     * EXCEPT the one named in $except — used both for the result set and to
     * compute facet options that would still return at least one result.
     */
    private function baseQuery(?string $except = null): Builder
    {
        if ($this->search !== '') {
            $ids = Artist::search($this->search)->keys();
            $query = Artist::query()->published()->whereIn('id', $ids);
        } else {
            $query = Artist::query()->published();
        }

        if ($except !== 'domain' && $this->filterDomain !== '') {
            $query->where('discipline', $this->filterDomain);
        }
        if ($except !== 'secondaryDomain' && $this->filterSecondaryDomain !== '') {
            $query->where('secondary_discipline', $this->filterSecondaryDomain);
        }
        if ($except !== 'locality' && $this->filterLocality !== '') {
            $query->where('city', $this->filterLocality);
        }
        if ($except !== 'activities' && $this->filterActivities !== []) {
            $query->where(function ($inner) {
                foreach ($this->filterActivities as $activity) {
                    $inner->orWhereJsonContains('activities', $activity);
                }
            });
        }
        if ($except !== 'secondaryActivities' && $this->filterSecondaryActivities !== []) {
            $query->where(function ($inner) {
                foreach ($this->filterSecondaryActivities as $activity) {
                    $inner->orWhereJsonContains('secondary_activities', $activity);
                }
            });
        }

        return $query;
    }

    public function render(): View
    {
        $query = $this->baseQuery();

        $query = match ($this->sort) {
            'recent' => $query->orderByDesc('published_at')->orderByDesc('id'),
            'z-name' => $query->orderByDesc('name'),
            'relevance' => $query,  // Scout already ordered by score when IDs resolved above
            default => $query->orderBy('name'),
        };

        $artists = $query->paginate(12);

        return view('livewire.public.artists-index', [
            'artists' => $artists,
            'total' => $artists->total(),
            'domains' => $this->presentColumnOptions('domain', 'discipline'),
            'secondaryDomains' => $this->presentColumnOptions('secondaryDomain', 'secondary_discipline'),
            // Main activities are only meaningful once a primary domain is selected
            // (see <x-ds.filter-modal>, which shows a placeholder message otherwise).
            'availableActivities' => $this->filterDomain !== '' ? $this->presentJsonValues('activities') : [],
            'availableSecondaryActivities' => $this->presentJsonValues('secondaryActivities', 'secondary_activities'),
            'localityGroups' => $this->localityGroups(),
            'filterCount' => $this->filterCount(),
            'suggestions' => $this->suggestions(),
            'suggestionResultCount' => $this->suggestionResultCount(),
        ]);
    }

    /**
     * Distinct non-empty values of a column across artists matching every
     * filter except this facet, as a [value => value] map.
     *
     * @return array<string, string>
     */
    private function presentColumnOptions(string $facet, string $column): array
    {
        return $this->baseQuery($facet)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->mapWithKeys(fn (string $value) => [$value => $value])
            ->all();
    }

    /**
     * Distinct values present in a JSON array column across artists matching
     * every filter except this facet.
     *
     * @return array<int, string>
     */
    private function presentJsonValues(string $facet, ?string $column = null): array
    {
        $column ??= $facet;

        return $this->baseQuery($facet)
            ->get([$column])
            ->flatMap(fn (Artist $artist) => $artist->{$column} ?? [])
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Present communes grouped by canton region, with out-of-canton communes
     * bucketed under "Hors canton". Only non-empty groups are returned.
     *
     * @return array<string, array<int, string>>
     */
    private function localityGroups(): array
    {
        $presentCities = $this->baseQuery('locality')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->all();

        $regionGroups = collect(config('localities.groups', []))->except('Autre');
        $cantonCommunes = $regionGroups->flatten()->all();

        $groups = [];
        foreach ($regionGroups as $region => $communes) {
            $present = array_values(array_intersect($communes, $presentCities));
            if (! empty($present)) {
                $groups[$region] = $present;
            }
        }

        $horsCanton = array_values(array_diff($presentCities, $cantonCommunes));
        if (! empty($horsCanton)) {
            $groups[config('localities.outside_canton_value', 'Hors canton')] = $horsCanton;
        }

        return $groups;
    }
}

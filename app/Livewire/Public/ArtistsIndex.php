<?php

namespace App\Livewire\Public;

use App\Database\Models\Activity;
use App\Database\Models\Artist;
use App\Database\Models\Discipline;
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

    private function baseQuery(?string $except = null): Builder
    {
        if ($this->search !== '') {
            $ids = Artist::search($this->search)->keys();
            $query = Artist::query()->published()->whereIn('id', $ids);
        } else {
            $query = Artist::query()->published();
        }

        if ($except !== 'domain' && $this->filterDomain !== '') {
            $query->where('discipline_main_id', (int) $this->filterDomain);
        }
        if ($except !== 'secondaryDomain' && $this->filterSecondaryDomain !== '') {
            $query->where('discipline_secondary', (int) $this->filterSecondaryDomain);
        }
        if ($except !== 'locality' && $this->filterLocality !== '') {
            $query->where('city', $this->filterLocality);
        }
        if ($except !== 'activities' && $this->filterActivities !== []) {
            $activityIds = array_map('intval', $this->filterActivities);
            $query->whereHas('registration', function ($q) use ($activityIds) {
                $q->whereHas('activities', fn ($inner) => $inner->whereIn('activities.id', $activityIds));
            });
        }
        if ($except !== 'secondaryActivities' && $this->filterSecondaryActivities !== []) {
            $activityIds = array_map('intval', $this->filterSecondaryActivities);
            $query->whereHas('registration', function ($q) use ($activityIds) {
                $q->whereHas('activities', fn ($inner) => $inner->whereIn('activities.id', $activityIds));
            });
        }

        return $query;
    }

    public function render(): View
    {
        $query = $this->baseQuery()->with(['disciplineMain', 'disciplineSecondary', 'registration.activities']);

        $query = match ($this->sort) {
            'recent' => $query->orderByDesc('created_at')->orderByDesc('id'),
            'z-name' => $query->orderByDesc('artist_name'),
            'relevance' => $query,
            default => $query->orderBy('artist_name'),
        };

        $artists = $query->paginate(12);

        return view('livewire.public.artists-index', [
            'artists' => $artists,
            'total' => $artists->total(),
            'domains' => $this->domainFacetOptions('domain'),
            'secondaryDomains' => $this->secondaryDomainFacetOptions('secondaryDomain'),
            'availableActivities' => $this->filterDomain !== '' ? $this->activityFacetOptions() : [],
            'availableSecondaryActivities' => $this->activityFacetOptions(),
            'localityGroups' => $this->localityGroups(),
            'filterCount' => $this->filterCount(),
            'suggestions' => $this->suggestions(),
            'suggestionResultCount' => $this->suggestionResultCount(),
        ]);
    }

    /**
     * Domain options for the filter modal: [id => label].
     *
     * @return array<string, string>
     */
    private function domainFacetOptions(string $facet): array
    {
        $ids = $this->baseQuery($facet)
            ->whereNotNull('discipline_main_id')
            ->distinct()
            ->pluck('discipline_main_id');

        return Discipline::whereIn('id', $ids)
            ->orderBy('label')
            ->pluck('label', 'id')
            ->mapWithKeys(fn ($label, $id) => [(string) $id => $label])
            ->all();
    }

    /**
     * Secondary domain options for the filter modal: [id => label].
     *
     * @return array<string, string>
     */
    private function secondaryDomainFacetOptions(string $facet): array
    {
        $ids = $this->baseQuery($facet)
            ->whereNotNull('discipline_secondary')
            ->distinct()
            ->pluck('discipline_secondary');

        return Discipline::whereIn('id', $ids)
            ->orderBy('label')
            ->pluck('label', 'id')
            ->mapWithKeys(fn ($label, $id) => [(string) $id => $label])
            ->all();
    }

    /**
     * Activity options for the selected domain: [id => label].
     *
     * @return array<string, string>
     */
    private function activityFacetOptions(): array
    {
        if (blank($this->filterDomain)) {
            return [];
        }

        return Activity::where('discipline_id', (int) $this->filterDomain)
            ->orderBy('label')
            ->pluck('label', 'id')
            ->mapWithKeys(fn ($label, $id) => [(string) $id => $label])
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

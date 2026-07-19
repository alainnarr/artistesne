<div class="flex flex-col"
     x-data
     x-init="if (new URLSearchParams(window.location.search).get('openFilters') === '1') {
         $nextTick(() => $dispatch('open-modal', { id: 'filters' }));
     }">
    {{-- Banner : titre + description --}}
    <x-ds.section variant="paper" padding="none" class="pt-16">
        <x-ds.hero
            variant="page"
            :title="$search !== '' || $filterCount > 0 ? 'Résultat de' : 'Annuaire des artistes'"
            :accent="$search !== '' || $filterCount > 0 ? 'recherche' : 'neuchâtelois·es'"
        >
            <x-slot:lead-slot>
                @if ($search === '' && $filterCount === 0)
                    <p>Artistes.ne réunit les profils des artistes professionnel·les ancré·es dans le canton. Découvrez leurs pratiques, explorez leurs univers, entrez en contact.</p>
                @endif
            </x-slot:lead-slot>

            <x-slot:action>
                <x-ds.search
                    variant="on-dark"
                    :label="$search !== '' || $filterCount > 0 ? '' : 'Rechercher dans l\'annuaire'"
                    :filters-label="$search !== '' || $filterCount > 0 ? 'Affiner la recherche' : 'Afficher les filtres'"
                    wire:model.live.debounce.300ms="draftSearch"
                    wire:keydown.enter="applySearch"
                    wire-submit="applySearch"
                    :value="$draftSearch"
                    :showFilters="true"
                    :filterCount="$filterCount"
                    :suggestions="$suggestions"
                    :resultCount="$suggestionResultCount"
                />
            </x-slot:action>
        </x-ds.hero>
    </x-ds.section>

    {{-- Filter modal --}}
    <x-ds.filter-modal
        :domains="$domains"
        :secondary-domains="$secondaryDomains"
        :locality-groups="$localityGroups"
        :available-activities="$availableActivities"
        :available-secondary-activities="$availableSecondaryActivities"
        :filter-domain="$filterDomain"
        :filter-secondary-domain="$filterSecondaryDomain"
        :filter-locality="$filterLocality"
        :filter-activities="$filterActivities"
        :filter-secondary-activities="$filterSecondaryActivities"
        :result-count="$total"
    />

    {{-- Bloc liste --}}
    <x-ds.section variant="cream">
        <div class="flex flex-col gap-8">

            @php
                $isSearchActive = $search !== '' || $filterCount > 0;
                if ($isSearchActive && $search !== '') {
                    $countLabel  = 'résultat';
                    $countSuffix = 'correspond' . ($total !== 1 ? 'ent' : '') . ' à « ' . $search . ' »'
                        . ($filterCount > 0 ? ', ' . $filterCount . ' filtre' . ($filterCount !== 1 ? 's' : '') . ' sélectionné' . ($filterCount !== 1 ? 's' : '') : '');
                } else {
                    $countLabel  = 'artiste';
                    $countSuffix = 'référencé·es';
                }
            @endphp
            @if ($artists->isNotEmpty())
                <x-ds.list-header
                    :title="$isSearchActive ? null : 'Découvrir les artistes'"
                    :count="$total"
                    :entity-label="$countLabel"
                    :entity-suffix="$countSuffix"
                    :sort-options="$search !== ''
                        ? ['relevance' => 'Pertinence', 'name' => 'Nom (A→Z)', 'z-name' => 'Nom (Z→A)', 'recent' => 'Plus récents']
                        : ['name' => 'Nom (A→Z)', 'z-name' => 'Nom (Z→A)', 'recent' => 'Plus récents']"
                    :sort-selected="$sort"
                    sort-wire-key="sort"
                />
            @endif

            @if ($artists->isEmpty())
                <div class="flex flex-col gap-4 py-4">
                    @if ($search !== '')
                        <p class="text-base font-normal text-brand">{{ $total }} {{ $total !== 1 ? 'résultats correspondent' : 'résultat correspond' }} à « {{ $search }} »</p>
                        <p class="text-base leading-relaxed text-brand">Aucun résultat ne correspond à votre recherche.</p>
                        <p class="text-base leading-relaxed text-brand">Nous vous invitons à reformuler votre requête ou à utiliser des termes plus généraux.</p>
                    @else
                        <p class="text-base leading-relaxed text-brand">Aucun artiste ne correspond aux filtres sélectionnés.</p>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($artists as $artist)
                        <x-ds.card-artist
                            wire:key="artist-card-{{ $artist->id }}"
                            :name="$artist->name"
                            :location="$artist->city ?? null"
                            :primary-domain="$artist->discipline"
                            :secondary-domain="$artist->secondary_discipline ?? null"
                            :activities="$artist->activities ?? []"
                            :href="route('public.artist.show', $artist)"
                            class="max-w-none"
                        />
                    @endforeach
                </div>

                <div>{{ $artists->links() }}</div>
            @endif
        </div>
    </x-ds.section>

    {{-- Bannière push --}}
    <x-ds.section variant="dark" padding="none">
        <x-ds.banner-push
            title="Se faire référencer sur la plateforme"
            description="Artiste professionnel·le dans le canton de Neuchâtel ? Rejoignez l'annuaire et ouvrez votre pratique à de nouveaux regards."
            cta-label="Espace artistes"
            :cta-href="route('public.artist-registration')"
        />
    </x-ds.section>
</div>

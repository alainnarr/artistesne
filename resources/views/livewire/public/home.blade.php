<div class="flex flex-col" x-data x-init="if (window.innerWidth >= 1920) $wire.initializePageSize(window.innerWidth)">
    {{-- Banner : titre + description + barre de recherche --}}
    <x-ds.section variant="paper" padding="none" class="pt-16">
        <x-ds.hero
            title="Annuaire des artistes"
            accent="neuchâtelois·es"
        >
            <x-slot:lead-slot>
                <p>
                    <span class="font-normal">Artistes.ne</span> réunit les profils des artistes professionnel·les ancré·es dans le canton. Découvrez leurs pratiques, explorez leurs univers, entrez en contact.
                </p>
            </x-slot:lead-slot>

            <x-slot:action>
                {{-- Home search navigates to the full listing page --}}
                <form method="GET" action="{{ route('public.artists.index') }}" class="w-full">
                    <x-ds.search
                        variant="on-dark"
                        :showFilters="true"
                        :searchIndexUrl="route('public.artists.index')"
                        :suggestions="$suggestions"
                        wire:model.live.debounce.400ms="homeSearch"
                        wire-target="homeSearch"
                        input-name="q"
                        filtersModalId="home-filters"
                    />
                </form>

                {{-- Filter button on home redirects to artists index with filter open --}}
                <script>
                    document.addEventListener('open-modal', function (e) {
                        if (e.detail?.id === 'home-filters') {
                            window.location = '{{ route('public.artists.index') }}?openFilters=1';
                        }
                    });
                </script>
            </x-slot:action>
        </x-ds.hero>
    </x-ds.section>

    {{-- Bloc liste : titre + tri + grille de cartes --}}
    <x-ds.section variant="cream">
        <div class="flex flex-col gap-8">
            <x-ds.list-header
                title="Découvrir les artistes"
                :count="$total"
                entity-label="artiste"
                entity-suffix="référencé·es"
                :sort-options="['random' => 'Aléatoire', 'recent' => 'Plus récents', 'name' => 'Nom (A→Z)']"
                :sort-selected="$sort"
                sort-wire-key="sort"
            />

            @if ($artists->isEmpty())
                <x-ds.empty-state
                    title="Aucun artiste"
                    description="Aucun artiste ne correspond à votre recherche."
                />
            @else
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($artists as $artist)
                        <x-ds.card-artist
                            wire:key="featured-artist-card-{{ $artist->id }}"
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

                @if ($hasMore)
                    <div class="flex justify-center pt-4">
                        <x-ds.btn type="button" variant="secondary" size="md" wire:click="showMore">
                            Afficher plus
                        </x-ds.btn>
                    </div>
                @endif
            @endif
        </div>
    </x-ds.section>

    {{-- Bannière push --}}
    <x-ds.section variant="dark" padding="none">
        <x-ds.banner-push
            title="Se faire référencer sur la plateforme"
            description="Artiste professionnel·le dans le canton de Neuchâtel ? Rejoignez les {{ $total }} artistes de l'annuaire et ouvrez votre pratique à de nouveaux regards."
            cta-label="Espace artistes"
            :cta-href="route('artist.register')"
        />
    </x-ds.section>
</div>

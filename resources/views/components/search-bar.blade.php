{{--
    Search Bar component — DS Annuaire des artistes
    Figma: https://www.figma.com/design/quwzQh6G272IDJtnfF7teN/DS--Annuaire-des-artistes?node-id=2439-38605

    Props:
        $value       — current search value (wire:model compatible)
        $placeholder — input placeholder text
        $showFilters — whether to display the "Afficher les filtres" toggle button
        $filterCount — number of active filters (shows badge when > 0)
        $suggestions — array of suggestion strings to render in the dropdown
        $resultCount — total result count shown in the dropdown footer
--}}
@props([
    'value'       => '',
    'placeholder' => 'Recherche par artistes, activités, mots-clés',
    'showFilters' => true,
    'filterCount' => 0,
    'suggestions' => [],
    'resultCount' => null,
])

@php
    $hasSuggestions = count($suggestions) > 0;
@endphp

<div class="w-full" x-data="{ focused: false }">

    {{-- Search bar container --}}
    <div class="relative flex h-14 w-full items-center gap-2 bg-brand-paper px-6"
         :class="focused ? 'ring-[1.5px] ring-brand-muted' : ''">

        {{-- Search input --}}
        <div class="flex flex-1 items-center gap-3 min-w-0">

            {{-- Magnifying-glass icon --}}
            <x-picto name="search" class="size-6 shrink-0 text-brand-muted" />

            <input
                {{ $attributes->whereStartsWith('wire:') }}
                type="search"
                autocomplete="off"
                value="{{ $value }}"
                placeholder="{{ $placeholder }}"
                @focus="focused = true"
                @blur="focused = false"
                class="flex-1 min-w-0 bg-transparent text-sm text-brand placeholder-brand-muted/70 outline-none"
            />

            {{-- Clear button — only when input has content --}}
            @if ($value)
                <button type="button" {{ $attributes->whereStartsWith('wire:') ? 'wire:click="clearSearch"' : '' }}
                        class="shrink-0 text-brand-muted transition hover:text-brand">
                    <x-picto name="close" class="size-5" />
                </button>
            @endif
        </div>

        {{-- Separator --}}
        @if ($showFilters)
            <div class="h-6 w-px shrink-0 bg-brand-hairline"></div>
        @endif

        {{-- Filter toggle button --}}
        @if ($showFilters)
            <button type="button"
                    class="flex shrink-0 items-center gap-1.5 text-sm text-brand-muted transition hover:text-brand">
                <x-picto name="filter" class="size-4" />
                <span class="hidden sm:inline">Afficher les filtres</span>
                @if ($filterCount > 0)
                    <span class="inline-flex size-5 items-center justify-center rounded-full bg-brand text-[10px] font-medium text-brand-paper">
                        {{ $filterCount }}
                    </span>
                @endif
            </button>
        @endif
    </div>

    {{-- Suggestions dropdown --}}
    @if ($hasSuggestions)
        <div class="relative z-20 w-full border-[1.5px] border-brand-muted bg-brand-paper pt-1"
             style="border-top: none">

            {{-- Suggestion list --}}
            <ul class="py-2">
                <li class="border-b border-brand-muted/25 px-6 pb-1">
                    <span class="text-xs font-light text-brand-muted">Suggestion de résultats</span>
                </li>
                @foreach ($suggestions as $suggestion)
                    <li>
                        <button type="button"
                                class="flex h-10 w-full items-center px-6 text-sm text-brand transition hover:bg-brand-track">
                            {{ $suggestion }}
                        </button>
                    </li>
                @endforeach
            </ul>

            {{-- Footer --}}
            @if ($resultCount !== null)
                <div class="flex h-[66px] items-center justify-center bg-brand px-8">
                    <a href="#" class="flex items-center gap-2 text-sm text-brand-paper underline underline-offset-2">
                        Voir les résultats ({{ $resultCount }})
                        <x-picto name="arrow-right" class="size-4" />
                    </a>
                </div>
            @endif
        </div>
    @endif

</div>

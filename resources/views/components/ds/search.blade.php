{{--
    DS · Search — Figma node 2439:38605
    https://www.figma.com/design/quwzQh6G272IDJtnfF7teN/?node-id=2439-38605

    Props :
        $variant         = 'on-dark' | 'on-light'
        $label           = libellé (null pour masquer)
        $value           = valeur initiale (sync depuis Livewire)
        $inputName       = name attr on the <input> (pour soumission de formulaire GET, ex: 'q')
        $wireTarget      = nom de la propriété Livewire à set() (défaut: 'search')
        $wireSubmit      = nom de la méthode Livewire à appeler lors de la validation (Enter / clic suggestion)
        $placeholder
        $showFilters     = afficher le bouton filtres
        $filterCount     = badge si > 0
        $filtersModalId  = id de la modal à ouvrir (défaut : 'filters')
        $suggestions     = array<string>|array<string,array<string>> — plat ou groupé par catégorie
        $resultCount     = int|null — footer suggestion layer
        $searchIndexUrl  = URL de base pour les liens de suggestions (ex : route('public.artists.index'))
--}}
@props([
    'variant'        => 'on-dark',
    'label'          => 'Rechercher dans l\'annuaire',
    'value'          => '',
    'inputName'      => null,
    'wireTarget'     => 'search',
    'wireSubmit'     => null,
    'placeholder'    => 'Recherche par artistes, activités, mots-clés',
    'showFilters'    => true,
    'filterCount'    => 0,
    'filtersLabel'   => 'Afficher les filtres',
    'filtersModalId' => 'filters',
    'suggestions'    => [],
    'resultCount'    => null,
    'searchIndexUrl' => null,
])

@php
    $onDark          = $variant === 'on-dark';
    $labelClass      = $onDark ? 'text-brand-paper' : 'text-brand';
    $filterTextClass = $onDark ? 'text-brand-paper' : 'text-brand';
    $isGrouped       = ! empty($suggestions) && is_string(array_key_first($suggestions));
    $hasSuggestions  = $isGrouped
        ? collect($suggestions)->flatten()->isNotEmpty()
        : count($suggestions) > 0;
@endphp

<div class="ds-search w-full"
     x-data="{
         focused: false,
         inputValue: @js($value),
         suggestionsOpen: @js($hasSuggestions),
         clearInput() {
             this.inputValue = '';
             this.suggestionsOpen = false;
             if (typeof $wire !== 'undefined') {
                 $wire.set(@js($wireTarget), '');
                 @if($wireSubmit) $wire.call(@js($wireSubmit)); @endif
             }
         },
         selectSuggestion(name) {
             this.inputValue = name;
             this.suggestionsOpen = false;
             if (typeof $wire !== 'undefined') {
                 $wire.set(@js($wireTarget), name);
                 @if($wireSubmit) $wire.call(@js($wireSubmit)); @endif
             }
         },
     }"
     x-init="
         $watch('inputValue', val => { if (val.length < 2) suggestionsOpen = false; });
     "
     @click.outside="suggestionsOpen = false">

    @if ($label)
        <label class="mb-2 block text-xs font-medium {{ $labelClass }}">{{ $label }}</label>
    @endif

    {{-- Ligne : input + bouton filtres --}}
    <div class="flex w-full items-stretch gap-4">
        <div class="relative flex h-14 flex-1 items-center gap-3 bg-brand-paper px-4"
             :class="focused ? 'border border-brand' : 'border border-transparent'">
            <input
                type="text"
                autocomplete="off"
                @if ($inputName) name="{{ $inputName }}" @endif
                :value="inputValue"
                x-on:input="inputValue = $event.target.value"
                placeholder="{{ $placeholder }}"
                @focus="focused = true"
                @blur="focused = false"
                {{ $attributes->whereStartsWith('wire:') }}
                class="flex-1 min-w-0 bg-transparent text-base text-brand placeholder-brand-muted outline-none"
            />

            {{-- Clear button — Alpine-driven, no server round-trip --}}
            <button type="button"
                    x-show="inputValue.length > 0"
                    x-cloak
                    @click="clearInput"
                    class="shrink-0 text-brand-muted transition hover:text-brand"
                    aria-label="Effacer la recherche">
                <x-picto name="close" class="size-5" />
            </button>

            <x-picto name="search" class="size-5 shrink-0 text-brand-muted" x-show="inputValue.length === 0" />
        </div>

        @if ($showFilters)
            <button type="button"
                    @click="$dispatch('open-modal', { id: @js($filtersModalId) })"
                    class="inline-flex h-14 shrink-0 items-center gap-2 px-2 text-sm {{ $filterTextClass }} hover:opacity-80 transition"
                    aria-label="{{ $filtersLabel }}">
                <x-picto name="filter" class="size-5" />
                <span class="hidden sm:inline">{{ $filtersLabel }}</span>
                @if ($filterCount > 0)
                    <x-ds.badge variant="light" size="sm">{{ $filterCount }}</x-ds.badge>
                @endif
            </button>
        @endif
    </div>

    {{-- Suggestion layer — rendered server-side when suggestions exist --}}
    @if ($hasSuggestions)
        <div x-show="suggestionsOpen"
             x-init="suggestionsOpen = true"
             x-cloak
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="relative z-20 mt-px w-full border border-brand-hairline bg-brand-paper shadow-lg">

            @if ($isGrouped)
                {{-- Grouped suggestions with category headers --}}
                <div class="max-h-72 overflow-y-auto divide-y divide-brand-hairline">
                    @foreach ($suggestions as $groupLabel => $groupItems)
                        <div wire:key="group-{{ $loop->index }}">
                            <p class="px-4 pt-3 pb-1 text-xs font-semibold uppercase tracking-wide text-brand-muted">{{ $groupLabel }}</p>
                            <ul class="pb-1">
                                @foreach ($groupItems as $item)
                                    <li wire:key="suggestion-{{ $loop->parent->index }}-{{ $loop->index }}">
                                        @if ($searchIndexUrl)
                                            <a href="{{ $searchIndexUrl }}?q={{ urlencode($item) }}"
                                               class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-brand transition-colors hover:bg-brand-cream sm:text-base">
                                                {{ $item }}
                                            </a>
                                        @else
                                            <button type="button"
                                                    @click="selectSuggestion(@js($item))"
                                                    class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-brand transition-colors hover:bg-brand-cream sm:text-base">
                                                {{ $item }}
                                            </button>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Flat suggestions list (home page, etc.) --}}
                <p class="px-4 pt-3 text-xs font-semibold uppercase tracking-wide text-brand-muted">Suggestions</p>
                <ul class="max-h-72 overflow-y-auto py-1">
                    @foreach ($suggestions as $suggestion)
                        <li wire:key="suggestion-{{ $loop->index }}">
                            @if ($searchIndexUrl)
                                <a href="{{ $searchIndexUrl }}?q={{ urlencode($suggestion) }}"
                                   class="block w-full px-4 py-2 text-left text-sm text-brand transition-colors hover:bg-brand-cream sm:text-base">
                                    {{ $suggestion }}
                                </a>
                            @else
                                <button type="button"
                                        @click="selectSuggestion(@js($suggestion))"
                                        class="block w-full px-4 py-2 text-left text-sm text-brand transition-colors hover:bg-brand-cream sm:text-base">
                                    {{ $suggestion }}
                                </button>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($resultCount !== null)
                <div class="border-t border-brand-hairline px-4 py-3 text-center">
                    @if ($searchIndexUrl)
                        <a href="{{ $searchIndexUrl }}?q={{ urlencode($value) }}"
                           class="inline-flex items-center gap-2 text-sm font-medium text-brand-teal hover:underline">
                            Voir les {{ $resultCount }} résultat{{ $resultCount > 1 ? 's' : '' }}
                            <x-picto name="arrow-right" class="size-4" />
                        </a>
                    @else
                        <button type="button"
                                @click="@if($wireSubmit) $wire.call(@js($wireSubmit)) @else suggestionsOpen = false @endif"
                                class="inline-flex items-center gap-2 text-sm font-medium text-brand-teal hover:underline">
                            Voir les {{ $resultCount }} résultat{{ $resultCount > 1 ? 's' : '' }}
                            <x-picto name="arrow-right" class="size-4" />
                        </button>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>

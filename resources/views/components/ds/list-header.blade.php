{{--
    DS · List header — bloc "titre de liste + nombre d'entités + tri"

    Inspiré du Figma 561:49046 (Bloc card list / header card list).

    Utilisé en haut des listings (Home, Artists index, etc.) pour afficher :
        - Un titre h2 serif (ex: "Découvrir les artistes")
        - Une ligne info à gauche (ex: "152 artistes référencé·es")
        - Un menu de tri à droite (<x-ds.sort-menu>)
        - Un séparateur fin (hairline) en bas

    Props :
        $title         : titre h2 (string, requis)
        $count         : nombre d'entités (int|null) — affiché si fourni
        $entityLabel   : libellé singulier de l'entité (ex: 'artiste')
        $entitySuffix  : suffixe après l'entité (ex: 'référencé·es')
        $sortOptions   : array [value => label] passé à <x-ds.sort-menu>
        $sortSelected  : valeur sélectionnée
        $sortWireKey   : nom de propriété Livewire pour wire:click (ex: 'sort')
        $sortLabel     : libellé du sort menu (défaut "Trier par")
--}}
@props([
    'title',
    'count' => null,
    'entityLabel' => 'élément',
    'entitySuffix' => '',
    'sortOptions' => [],
    'sortSelected' => null,
    'sortWireKey' => null,
    'sortLabel' => 'Trier par',
])

@php
    $plural = $count !== null ? \Illuminate\Support\Str::plural($entityLabel, (int) $count) : null;
@endphp

<header {{ $attributes->class(['ds-list-header flex flex-col gap-4 sm:gap-6']) }}>
    <h2 class="font-serif text-3xl font-bold leading-tight text-brand sm:text-4xl">
        {{ $title }}
    </h2>

    <div class="flex flex-col gap-3 border-b border-brand pb-4 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        @if ($count !== null)
            <p class="text-base font-normal text-brand">
                {{ $count }} {{ $plural }}{{ $entitySuffix ? ' '.$entitySuffix : '' }}
            </p>
        @else
            <span></span>
        @endif

        @if (! empty($sortOptions))
            <x-ds.sort-menu
                :label="$sortLabel"
                :options="$sortOptions"
                :selected="$sortSelected"
                :wire-key="$sortWireKey"
            />
        @endif
    </div>
</header>

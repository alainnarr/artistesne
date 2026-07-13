{{--
    DS · Filter Modal — Figma node 2275:11730
    https://www.figma.com/design/quwzQh6G272IDJtnfF7teN/?node-id=2275-11730

    Anatomy : <x-ds.modal id="filters"> + <x-ds.accordion> sections + toggle-chip radio/checkboxes + fixed footer.

    Props :
        $domains                      = array<string, string>  — [label => label] from TaxonomyTerm::domainOptions()
        $localities                   = Collection<string>     — distinct city values
        $availableActivities          = array<string>          — activity names for the selected primary domain
                                                                   (empty when no domain is selected — a placeholder
                                                                   message is shown instead, per Figma node 561:65560/65633)
        $availableSecondaryActivities = array<string>          — activity names for the selected secondary domain
        $filterDomain                 = string                 — selected primary domain (single, wire:model bound in parent)
        $filterSecondaryDomain        = string                 — selected secondary domain (single)
        $filterLocality               = string
        $filterActivities             = array<int, string>
        $filterSecondaryActivities    = array<int, string>
        $resultCount                  = int — live result count for footer CTA
--}}
@props([
    'domains'                      => [],
    'secondaryDomains'             => [],
    'localityGroups'               => [],
    'availableActivities'          => [],
    'availableSecondaryActivities' => [],
    'filterDomain'                 => '',
    'filterSecondaryDomain'        => '',
    'filterLocality'               => '',
    'filterActivities'             => [],
    'filterSecondaryActivities'    => [],
    'resultCount'                  => 0,
])

@php
    $primaryCount      = $filterDomain !== '' ? 1 : 0;
    $secondaryCount    = $filterSecondaryDomain !== '' ? 1 : 0;
    $activitiesCount   = count($filterActivities);
    $secondaryActCount = count($filterSecondaryActivities);
    $localityCount     = $filterLocality !== '' ? 1 : 0;
    $totalCount        = $primaryCount + $secondaryCount + $activitiesCount + $secondaryActCount + $localityCount;
@endphp

<x-ds.modal id="filters" title="Filtres" size="lg">
    <x-slot:footer>
        <div class="flex items-center justify-between gap-4">
            @if ($totalCount > 0)
                <button
                    type="button"
                    wire:click="clearFilters"
                    class="text-sm font-medium text-brand-teal underline-offset-2 hover:underline"
                >
                    Effacer les filtres ({{ $totalCount }})
                </button>
            @else
                <span></span>
            @endif

            <button
                type="button"
                @click="$dispatch('close-modal', { id: 'filters' })"
                class="inline-flex h-14 items-center justify-center gap-2 bg-brand-mint px-6 text-sm font-semibold text-brand transition hover:bg-brand-mint-hover"
            >
                Afficher {{ $resultCount }} artiste{{ $resultCount > 1 ? 's' : '' }}
                <x-picto name="arrow-right" class="size-4" />
            </button>
        </div>
    </x-slot:footer>

    {{-- ── Domaine principal (single-select radio) ─────────────── --}}
    @if (count($domains) > 0)
    <x-ds.accordion
        title="Domaine principal"
        :open="true"
        :active-count="$primaryCount"
    >
        <div class="flex flex-wrap gap-2 py-3">
            @foreach ($domains as $value => $label)
                @php $isChecked = $filterDomain === $value; @endphp
                {{-- Click on an already-selected chip deselects it --}}
                <label
                    class="inline-flex cursor-pointer items-center gap-2 border px-3 py-1.5 text-sm font-medium transition-colors
                           {{ $isChecked ? 'border-brand bg-brand text-brand-paper' : 'border-brand-muted text-brand hover:border-brand hover:bg-brand-cream' }}"
                    @if ($isChecked) @click="$wire.set('filterDomain', ''); $event.preventDefault()" @endif
                >
                    <input
                        type="radio"
                        wire:model.live="filterDomain"
                        value="{{ $value }}"
                        class="sr-only"
                    />
                    @if ($isChecked)
                        <x-picto name="check" class="size-3.5 shrink-0" />
                    @endif
                    {{ $label }}
                </label>
            @endforeach
        </div>
    </x-ds.accordion>
    @endif

    {{-- ── Activité principale (only selectable once a primary domain is chosen) ── --}}
    @if (count($domains) > 0)
        <x-ds.accordion
            title="Activité principale"
            :open="true"
            :active-count="$activitiesCount"
        >
            @if ($filterDomain === '')
                <div class="bg-brand-track p-6 text-base leading-relaxed text-brand">
                    Pour afficher les activités principales, vous devez sélectionner un domaine.
                </div>
            @else
                <div class="flex flex-wrap gap-2 py-3">
                    @foreach ($availableActivities as $activity)
                        @php $isChecked = in_array($activity, $filterActivities, true); @endphp
                        <label class="inline-flex cursor-pointer items-center gap-2 border px-3 py-1.5 text-sm font-medium transition-colors
                                      {{ $isChecked ? 'border-brand bg-brand text-brand-paper' : 'border-brand-muted text-brand hover:border-brand hover:bg-brand-cream' }}">
                            <input
                                type="checkbox"
                                wire:model.live="filterActivities"
                                value="{{ $activity }}"
                                class="sr-only"
                            />
                            @if ($isChecked)
                                <x-picto name="check" class="size-3.5 shrink-0" />
                            @endif
                            {{ $activity }}
                        </label>
                    @endforeach
                </div>
            @endif
        </x-ds.accordion>
    @endif

    {{-- ── Domaine secondaire (single-select radio) ────────────── --}}
    @if (count($secondaryDomains) > 0)
    <x-ds.accordion
        title="Domaine secondaire"
        :open="$secondaryCount > 0"
        :active-count="$secondaryCount"
    >
        <div class="flex flex-wrap gap-2 py-3">
            @foreach ($secondaryDomains as $value => $label)
                @php $isChecked = $filterSecondaryDomain === $value; @endphp
                <label
                    class="inline-flex cursor-pointer items-center gap-2 border px-3 py-1.5 text-sm font-medium transition-colors
                           {{ $isChecked ? 'border-brand bg-brand text-brand-paper' : 'border-brand-muted text-brand hover:border-brand hover:bg-brand-cream' }}"
                    @if ($isChecked) @click="$wire.set('filterSecondaryDomain', ''); $event.preventDefault()" @endif
                >
                    <input
                        type="radio"
                        wire:model.live="filterSecondaryDomain"
                        value="{{ $value }}"
                        class="sr-only"
                    />
                    @if ($isChecked)
                        <x-picto name="check" class="size-3.5 shrink-0" />
                    @endif
                    {{ $label }}
                </label>
            @endforeach
        </div>
    </x-ds.accordion>
    @endif

    {{-- ── Activité secondaire (visible only when a secondary domain is selected) ── --}}
    @if (count($availableSecondaryActivities) > 0)
        <x-ds.accordion
            title="Activité secondaire"
            :open="$secondaryActCount > 0"
            :active-count="$secondaryActCount"
        >
            <div class="flex flex-wrap gap-2 py-3">
                @foreach ($availableSecondaryActivities as $activity)
                    @php $isChecked = in_array($activity, $filterSecondaryActivities, true); @endphp
                    <label class="inline-flex cursor-pointer items-center gap-2 border px-3 py-1.5 text-sm font-medium transition-colors
                                  {{ $isChecked ? 'border-brand bg-brand text-brand-paper' : 'border-brand-muted text-brand hover:border-brand hover:bg-brand-cream' }}">
                        <input
                            type="checkbox"
                            wire:model.live="filterSecondaryActivities"
                            value="{{ $activity }}"
                            class="sr-only"
                        />
                        @if ($isChecked)
                            <x-picto name="check" class="size-3.5 shrink-0" />
                        @endif
                        {{ $activity }}
                    </label>
                @endforeach
            </div>
        </x-ds.accordion>
    @endif

    {{-- ── Commune de résidence ──────────────────────────────────── --}}
    @if (count($localityGroups) > 0)
        <x-ds.accordion
            title="Commune de résidence"
            :open="$localityCount > 0"
            :active-count="$localityCount"
        >
            <div class="flex flex-col gap-4 py-3">
                @foreach ($localityGroups as $region => $communes)
                    <div class="flex flex-col gap-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-muted">{{ $region }}</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($communes as $locality)
                                @php $isChecked = $filterLocality === $locality; @endphp
                                <label class="inline-flex cursor-pointer items-center gap-2 border px-3 py-1.5 text-sm font-medium transition-colors
                                              {{ $isChecked ? 'border-brand bg-brand text-brand-paper' : 'border-brand-muted text-brand hover:border-brand hover:bg-brand-cream' }}">
                                    <input
                                        type="radio"
                                        wire:model.live="filterLocality"
                                        value="{{ $locality }}"
                                        class="sr-only"
                                    />
                                    @if ($isChecked)
                                        <x-picto name="check" class="size-3.5 shrink-0" />
                                    @endif
                                    {{ $locality }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                @if ($filterLocality !== '')
                    <button type="button" wire:click="$set('filterLocality', '')"
                            class="self-start border border-brand-hairline px-3 py-1.5 text-sm text-brand-muted hover:text-brand transition-colors">
                        Toutes les communes
                    </button>
                @endif
            </div>
        </x-ds.accordion>
    @endif
</x-ds.modal>

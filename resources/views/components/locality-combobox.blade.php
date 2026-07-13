@props([
    'groups' => [],
    'placeholder' => 'Sélectionner…',
    'searchPlaceholder' => 'Commencer à taper pour chercher …',
    'label' => null,
    'required' => false,
])

{{--
    Combobox de localité (Alpine) : liste groupée par région, recherche dès la
    première lettre, gestion de la hauteur du panneau via une position fixe et
    un calcul de l'espace disponible sous le champ.

    Se lie à une propriété Livewire via wire:model (entangle) sur l'attribut
    "wire:model" passé au composant.
--}}
<div
    x-data="localityCombobox({
        groups: @js($groups),
        model: @entangle($attributes->wire('model')),
    })"
    x-on:keydown.escape.window="close()"
    class="relative"
>
    {{-- Champ déclencheur avec étiquette flottante. --}}
    <div class="relative">
        <button
            type="button"
            x-ref="trigger"
            x-on:click="toggle()"
            :aria-expanded="open"
            aria-haspopup="listbox"
            class="flex h-14 w-full items-center justify-between rounded-[2px] border bg-brand-paper px-4 text-left text-base text-brand transition-colors focus:outline-none"
            :class="open ? 'border-brand-teal' : 'border-brand-muted'"
        >
            <span x-text="selected"></span>
            <span class="inline-flex text-brand-muted transition" :class="open ? 'rotate-180' : ''">
                <x-picto name="caret-down" class="size-4 shrink-0" />
            </span>
        </button>

        <label
            x-bind:class="(selected || open) ? 'top-0 -translate-y-1/2 text-[12px] leading-[17px] font-medium text-brand-teal' : 'top-1/2 -translate-y-1/2 text-[16px] leading-[24px] text-brand-muted'"
            class="pointer-events-none absolute start-3 bg-brand-paper px-1 leading-none transition-all duration-150"
        >
            {{ $label }}@if ($required)<span aria-hidden="true"> *</span>@endif
        </label>
    </div>

    {{-- Panneau --}}
    <div
        x-show="open"
        x-cloak
        x-transition.opacity
        x-ref="panel"
        x-on:click.outside="close()"
        class="absolute z-50 mt-1 w-full overflow-hidden rounded-[2px] border border-brand-hairline bg-brand-paper shadow-lg"
        :style="panelStyle"
    >
        {{-- Recherche --}}
        <div class="border-b border-brand-hairline p-2">
            <input
                x-ref="search"
                x-model="search"
                type="text"
                :placeholder="@js($searchPlaceholder)"
                class="w-full rounded-[2px] border border-transparent bg-brand-cream px-2 py-1.5 text-sm text-brand placeholder:text-brand-muted focus:border-brand-muted focus:bg-brand-paper focus:outline-none"
            >
        </div>

        {{-- Options groupées --}}
        <ul role="listbox" class="max-h-[var(--locality-list-height)] overflow-y-auto py-1" :style="`--locality-list-height: ${listMaxHeight}px`">
            <template x-for="(communes, region) in filteredGroups" :key="region">
                <li>
                    <p class="px-3 pb-1 pt-2 text-xs font-semibold uppercase tracking-wide text-brand-muted" x-text="region"></p>
                    <template x-for="commune in communes" :key="commune">
                        <button
                            type="button"
                            role="option"
                            x-on:click="choose(commune)"
                            :class="commune === selected ? 'bg-brand-mint/30' : ''"
                            class="block w-full px-5 py-1.5 text-left text-sm text-brand hover:bg-brand-mint/20"
                            x-text="commune"
                        ></button>
                    </template>
                </li>
            </template>
            <li x-show="isEmpty" class="px-3 py-3 text-sm text-brand-muted">Aucun résultat.</li>
        </ul>
    </div>

    @error($attributes->wire('model')->value())
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>


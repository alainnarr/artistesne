{{--
    DS · Sort menu — listbox déroulante (Alpine).

    Reproduit le pattern « Trier par » utilisé sur la Home (Figma 561:49042)
    et la page Liste des artistes. Sémantique listbox / aria-* + click outside.

    Props :
        $label    : libellé du bouton (défaut « Trier par »)
        $options  : associatif [value => label]
        $selected : valeur sélectionnée (pour mettre en gras l'option active)
        $wireKey  : nom de la propriété Livewire mise à jour (`$set('sort', ...)`).
                    Si null, les options émettent un évènement DOM 'ds-sort-change'
                    avec le payload {value}.
        $align    : 'left' | 'right' (défaut 'right')
--}}
@props([
    'label'    => 'Trier par',
    'options'  => [],
    'selected' => null,
    'wireKey'  => null,
    'align'    => 'right',
])

@php
    $menuAlign = $align === 'left' ? 'left-0' : 'right-0';
@endphp

<div {{ $attributes->class(['ds-sort-menu relative']) }}
     x-data="{ open: false }"
     @click.outside="open = false"
     @keydown.escape.window="open = false">
    <button
        type="button"
        @click="open = ! open"
        class="inline-flex items-center gap-2 text-sm text-brand transition hover:opacity-80"
        aria-haspopup="listbox"
        :aria-expanded="open ? 'true' : 'false'"
    >
        <span>{{ $label }}</span>
        <x-picto name="caret-down" class="size-4 transition" ::class="open ? 'rotate-180' : ''" />
    </button>

    <ul
        x-show="open"
        x-cloak
        x-transition.origin.top.right
        class="absolute {{ $menuAlign }} z-20 mt-2 w-48 bg-brand-paper py-2 shadow-[0_4px_16px_0_rgba(27,62,61,0.10)]"
        role="listbox"
    >
        @foreach ($options as $value => $optionLabel)
            <li>
                <button
                    type="button"
                    role="option"
                    aria-selected="{{ $selected === $value ? 'true' : 'false' }}"
                    @click="open = false"
                    @if ($wireKey)
                        wire:click="$set('{{ $wireKey }}', '{{ $value }}')"
                    @else
                        @click.prepend="$dispatch('ds-sort-change', { value: '{{ $value }}' })"
                    @endif
                    class="block w-full px-4 py-2 text-left text-sm text-brand hover:bg-brand-cream {{ $selected === $value ? 'font-medium' : '' }}"
                >{{ $optionLabel }}</button>
            </li>
        @endforeach
    </ul>
</div>

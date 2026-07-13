{{--
    DS · Accordion — Figma node 2439:39210
    https://www.figma.com/design/quwzQh6G272IDJtnfF7teN/?node-id=2439-39210

    Anatomie : container · title · badge (compteur de filtres actifs) · icône expand

    Props :
        $title       — libellé visible
        $level       = 'level-1' (h36) | 'level-2' (h32)
        $open        — ouvert au chargement
        $activeCount — int > 0 affiche un badge à droite du titre
--}}
@props([
    'title',
    'level'       => 'level-1',
    'open'        => false,
    'activeCount' => 0,
])

@php
    $padding = $level === 'level-2' ? 'py-2 px-3 text-sm' : 'py-3 px-4 text-base';
@endphp

<div x-data="{ open: @js($open) }" {{ $attributes->class('ds-accordion w-full border-b border-brand-hairline') }}>
    <button
        type="button"
        @click="open = !open"
        :aria-expanded="open"
        class="flex w-full items-center justify-between {{ $padding }} text-left font-medium text-brand hover:bg-brand-mint-soft transition-colors"
    >
        <span class="inline-flex items-center gap-2">
            {{ $title }}
            @if ($activeCount > 0)
                <x-ds.badge variant="light" size="sm">{{ $activeCount }}</x-ds.badge>
            @endif
        </span>
        <x-picto name="caret-down" class="size-4 shrink-0 transition-transform" ::class="open ? 'rotate-180' : ''" />
    </button>

    <div x-show="open" x-collapse x-cloak class="{{ $padding }} pt-0">
        {{ $slot }}
    </div>
</div>

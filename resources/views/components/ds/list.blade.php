{{--
    DS · List — Figma node 2439:39018
    https://www.figma.com/design/quwzQh6G272IDJtnfF7teN/?node-id=2439-39018

    Liste à puces typographique du DS.
    - 'canvas' : variante paragraphe (bullet rond brand)
    - 'card'   : variante carte (em-dash neutre, ex: activités d'un artiste)

    Props :
        $items   — array<string>
        $variant = 'canvas' | 'card'
--}}
@props([
    'items'   => [],
    'variant' => 'canvas',
])

@php
    $isCard = $variant === 'card';
@endphp

<ul {{ $attributes->class('ds-list flex flex-col gap-2 '.($isCard ? '' : 'list-disc list-inside marker:text-brand')) }}>
    @if ($items)
        @foreach ($items as $item)
            <li class="{{ $isCard ? 'flex items-baseline gap-2 text-base text-brand' : 'text-base text-brand' }}">
                @if ($isCard)
                    <span class="text-brand-muted" aria-hidden="true">—</span>
                @endif
                {{ $item }}
            </li>
        @endforeach
    @else
        {{ $slot }}
    @endif
</ul>

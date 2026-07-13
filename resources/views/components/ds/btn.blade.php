{{--
    DS · Button — Figma node 2274:12498
    https://www.figma.com/design/quwzQh6G272IDJtnfF7teN/?node-id=2274-12498

    Anatomy: container + (leading icon) + label + (trailing icon)
    Variants: primary (mint), secondary (white + mint border)
    Sizes  : lg (56px), md (42px, default), sm (32px)
    States : default / hover / pressed / focus / disabled (CSS pseudo-classes)

    Props:
        $variant = 'primary' | 'secondary'
        $size    = 'lg' | 'md' | 'sm'
        $icon         — picto name (leading)
        $iconTrailing — picto name (trailing)
        $iconSet      — picto set, default 'icons'
        $href         — render as <a> if provided
        $type         — button type, default 'button'

    Toutes les classes Tailwind sont scopées sur le composant — pas de surcharge globale.
--}}
@props([
    'variant'      => 'primary',
    'size'         => 'md',
    'icon'         => null,
    'iconTrailing' => null,
    'iconSet'      => 'icons',
    'href'         => null,
    'type'         => 'button',
])

@php
    // Sizes (h, px, text — strict Figma).
    $sizeClasses = [
        'lg' => 'h-14 px-6 text-base gap-2',   // 56 × auto, label gap 8, padding gap M=32 visuel
        'md' => 'h-[42px] px-5 text-base gap-2',
        'sm' => 'h-8 px-4 text-sm gap-1.5',
    ][$size] ?? 'h-[42px] px-5 text-base gap-2';

    // Variants — couleurs strictement issues des tokens Figma.
    $variantClasses = [
        // Primary: BG mint #bfeceb / Text #2e3d3c / Hover BG #496361 + Text white
        // Pressed: BG brand / Text white / Focus: same BG + border brand
        'primary' => implode(' ', [
            'bg-brand-mint text-brand border border-transparent',
            'hover:bg-brand-hover hover:text-brand-paper',
            'active:bg-brand active:text-brand-paper',
            'focus-visible:bg-brand-mint focus-visible:text-brand focus-visible:border-brand focus-visible:outline-none',
            'disabled:bg-brand-track disabled:text-brand-muted disabled:border-transparent disabled:cursor-not-allowed disabled:hover:bg-brand-track disabled:hover:text-brand-muted',
        ]),
        // Secondary: BG white / Border mint / Text brand
        // Hover: BG mint / Text brand / Pressed: BG brand text white
        // Focus: border brand. Disabled: border #a3a7a7 text muted
        'secondary' => implode(' ', [
            'bg-brand-paper text-brand border border-brand-mint',
            'hover:bg-brand-mint hover:text-brand',
            'active:bg-brand active:text-brand-paper active:border-brand',
            'focus-visible:bg-brand-paper focus-visible:text-brand focus-visible:border-brand focus-visible:outline-none',
            'disabled:bg-brand-paper disabled:text-brand-muted disabled:border-brand-soft disabled:cursor-not-allowed disabled:hover:bg-brand-paper disabled:hover:text-brand-muted',
        ]),
    ][$variant] ?? '';

    $iconSize = $size === 'sm' ? 'size-4' : 'size-5';

    $base = 'inline-flex items-center justify-center font-medium leading-none rounded-none whitespace-nowrap transition-colors duration-150 select-none';
    $classes = trim($base.' '.$sizeClasses.' '.$variantClasses);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon)
            <x-picto :name="$icon" :set="$iconSet" :class="$iconSize.' shrink-0'" />
        @endif
        <span>{{ $slot }}</span>
        @if ($iconTrailing)
            <x-picto :name="$iconTrailing" :set="$iconSet" :class="$iconSize.' shrink-0'" />
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        @if ($icon)
            <x-picto :name="$icon" :set="$iconSet" :class="$iconSize.' shrink-0'" />
        @endif
        <span>{{ $slot }}</span>
        @if ($iconTrailing)
            <x-picto :name="$iconTrailing" :set="$iconSet" :class="$iconSize.' shrink-0'" />
        @endif
    </button>
@endif

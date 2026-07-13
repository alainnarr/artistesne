{{--
    DS · Badge — Figma node 2289:14466
    https://www.figma.com/design/quwzQh6G272IDJtnfF7teN/?node-id=2289-14466

    Pastille numérique compacte (compteur de filtres actifs, notifications, etc.)

    Variantes (fond / texte) :
      - 'dark'  : brand / paper  (par défaut)
      - 'light' : mint / brand
      - 'soft'  : mint-soft / brand

    Props :
        $variant = 'dark' | 'light' | 'soft'
        $size    = 'sm' (16px) | 'md' (20px, défaut) | 'lg' (24px)
--}}
@props([
    'variant' => 'dark',
    'size'    => 'md',
])

@php
    $sizes = [
        'sm' => 'size-4 text-[10px]',
        'md' => 'size-5 text-[10px]',
        'lg' => 'size-6 text-xs',
    ][$size] ?? 'size-5 text-[10px]';

    $variants = [
        'dark'  => 'bg-brand text-brand-paper',
        'light' => 'bg-brand-mint text-brand',
        'soft'  => 'bg-brand-mint-soft text-brand',
    ][$variant] ?? 'bg-brand text-brand-paper';
@endphp

<span {{ $attributes->class('inline-flex items-center justify-center rounded-full font-medium leading-none '.$sizes.' '.$variants) }}>
    {{ $slot }}
</span>

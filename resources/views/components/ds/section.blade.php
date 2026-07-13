{{--
    DS · Section — wrapper de section de page.

    Fournit le fond, le conteneur centré et les paddings responsives
    utilisés sur l'ensemble des pages publiques (Home, About, Liste,
    Fiche, Inscription, etc.).

    Variants de fond :
        - 'paper' (défaut) : --color-brand-paper
        - 'cream'          : --color-brand-cream
        - 'dark'           : --color-brand (texte clair appliqué)

    Tailles de conteneur :
        - 'default' (max-w-6xl, défaut)
        - 'narrow'  (max-w-3xl, pour les contenus textuels long-form)
        - 'wide'    (max-w-[1216px], pour les grilles de cartes)
        - 'none'    (pas de conteneur interne — le slot gère lui-même)

    Padding vertical :
        - 'default' (py-12 sm:py-16, défaut)
        - 'tight'   (py-6 sm:py-10)
        - 'loose'   (py-16 sm:py-24)
        - 'none'    (aucun)
--}}
@props([
    'variant'   => 'paper',
    'container' => 'default',
    'padding'   => 'default',
    'as'        => 'section',
])

@php
    $bgClass = match ($variant) {
        'cream' => 'bg-brand-cream text-brand',
        'dark'  => 'bg-brand text-brand-paper',
        default => 'bg-brand-paper text-brand',
    };

    $containerClass = match ($container) {
        'narrow' => 'mx-auto w-full max-w-3xl px-6 sm:px-10',
        'wide'   => 'mx-auto w-full max-w-[1216px] px-6 sm:px-20',
        'none'   => null,
        default  => 'mx-auto w-full max-w-6xl px-6 sm:px-20',
    };

    $paddingClass = match ($padding) {
        'tight'  => 'py-6 sm:py-10',
        'loose'  => 'py-16 sm:py-24',
        'none'   => '',
        default  => 'py-12 sm:py-16',
    };
@endphp

<{{ $as }} {{ $attributes->class(['ds-section w-full', $bgClass, $paddingClass]) }}>
    @if ($containerClass)
        <div class="{{ $containerClass }}">
            {{ $slot }}
        </div>
    @else
        {{ $slot }}
    @endif
</{{ $as }}>

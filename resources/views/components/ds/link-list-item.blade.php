{{--
    DS · Link list item — Figma master-Link-list (instances 561:48723…)

    Ligne d'une liste de liens avec :
      - Carré mint (icône) à gauche, contenant un <x-picto> (défaut: external-link)
      - Texte du lien à droite

    Si $href est fourni, l'élément est un <a> cliquable et ouvert dans
    un nouvel onglet par défaut (rel=noopener).

    Props :
        $href     — URL cible (string|null)
        $icon     — nom de picto (string, défaut 'external-link')
        $iconSet  — set de picto (string, défaut 'icons')
        $external — bool, ouvre dans un nouvel onglet (défaut true si href)
--}}
@props([
    'href' => null,
    'icon' => 'external-link',
    'iconSet' => 'icons',
    'external' => true,
])

@php
    // Defense-in-depth: only allow http/https URLs to prevent javascript: scheme injection.
    $safeHref = ($href && preg_match('/^https?:\/\//i', $href)) ? $href : null;
    $tag = $safeHref ? 'a' : 'div';
    $isExternal = $safeHref && $external;
@endphp

<{{ $tag }}
    @if ($safeHref)
        href="{{ $safeHref }}"
        @if ($isExternal) target="_blank" rel="noopener" @endif
    @endif
    {{ $attributes->class('ds-link-list-item group inline-flex items-center gap-3 text-base text-brand transition hover:text-brand-teal') }}
>
    <span class="inline-flex size-7 shrink-0 items-center justify-center bg-brand-mint text-brand transition group-hover:bg-brand-mint-hover" aria-hidden="true">
        <x-picto :name="$icon" :set="$iconSet" class="size-4" />
    </span>
    <span class="underline-offset-2 group-hover:underline">{{ $slot }}</span>
</{{ $tag }}>

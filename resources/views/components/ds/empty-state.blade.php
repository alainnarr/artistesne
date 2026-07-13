{{--
    DS · Empty state — bloc « aucun résultat / page vide ».

    Pattern utilisé sur la Home (aucun artiste ne correspond),
    la liste filtrée, les confirmations magic-link, etc.

    Comprend :
        - Icône optionnelle (slot 'icon' — ex: <x-picto name="search" />)
        - Titre optionnel (prop)
        - Description (prop string OU slot 'body' pour HTML riche)
        - Actions optionnelles (slot par défaut — ex: <x-ds.btn>)

    Props :
        $title       : titre (string|null)
        $description : description simple (string|null)
        $tone        : 'paper' (défaut) | 'cream' — couleur de fond du bloc
        $align       : 'center' (défaut) | 'left'

    Slots :
        $icon  : icône en haut du bloc
        $body  : description HTML (alternative à $description)
        $slot  : actions (boutons CTA) sous la description
--}}
@props([
    'title'       => null,
    'description' => null,
    'tone'        => 'paper',
    'align'       => 'center',
])

@php
    $bgClass = $tone === 'cream' ? 'bg-brand-cream' : 'bg-brand-paper';
    $alignClass = $align === 'left'
        ? 'items-start text-left'
        : 'items-center text-center';
    $hasIcon = isset($icon) ? trim($icon) !== '' : false;
    $hasBody = isset($body) ? trim($body) !== '' : false;
    $hasActions = trim($slot) !== '';
@endphp

<div {{ $attributes->class(['ds-empty-state flex w-full flex-col gap-4 p-10', $bgClass, $alignClass]) }}>
    @if ($hasIcon)
        <span class="inline-flex size-12 items-center justify-center bg-brand-mint text-brand-teal">
            {{ $icon }}
        </span>
    @endif

    @if ($title)
        <h3 class="font-serif text-xl font-bold text-brand sm:text-2xl">{{ $title }}</h3>
    @endif

    @if ($hasBody)
        <div class="max-w-xl text-base text-brand-muted">{{ $body }}</div>
    @elseif ($description)
        <p class="max-w-xl text-base text-brand-muted">{{ $description }}</p>
    @endif

    @if ($hasActions)
        <div class="mt-2 flex flex-wrap gap-3 {{ $align === 'left' ? 'justify-start' : 'justify-center' }}">
            {{ $slot }}
        </div>
    @endif
</div>

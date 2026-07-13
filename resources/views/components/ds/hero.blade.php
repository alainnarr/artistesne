{{--
    DS · Hero — bloc d'introduction haut de page.

    Inspiré du hero Home (Figma 561:49042) :
        - Titre serif (Lora Bold)
        - Accent italique teal (Lora SemiBold Italic)
        - Paragraphe d'introduction
        - Slot optionnel pour un panneau d'action attaché en bas
          (barre de recherche sombre, CTA, etc.)

    Variants de taille :
        - 'home' (défaut) : titre 4xl/5xl, intro 2xl
        - 'page'          : titre 3xl/4xl, intro lg (pour les pages
          internes : À propos, Inscription, Liste des artistes, etc.)

    Props :
        $title       : titre principal (string)
        $accent      : fragment italique teal (string|null) — affiché
                       sous le titre, sur sa propre ligne
        $lead        : paragraphe d'intro (string|null)
        $leadSlot    : slot 'lead' — utiliser si le paragraphe nécessite
                       du HTML (spans, balises)
        $action      : slot par défaut — panneau attaché en bas du hero
                       (ex: <x-ds.search variant="on-dark" />)

    Le slot d'action garde son padding/coins par défaut. Si non fourni,
    le hero est tout simplement un bloc titre + lead.
--}}
@props([
    'variant' => 'home',
    'title'   => null,
    'accent'  => null,
    'lead'    => null,
])

@php
    $isHome = $variant === 'home';

    $titleClass = $isHome
        ? 'font-serif text-4xl font-bold leading-tight text-brand sm:text-5xl'
        : 'font-serif text-3xl font-bold leading-tight text-brand sm:text-4xl';

    $accentClass = $isHome
        ? 'font-serif text-4xl font-semibold italic leading-tight text-brand-teal sm:text-5xl'
        : 'font-serif text-3xl font-semibold italic leading-tight text-brand-teal sm:text-4xl';

    $leadClass = $isHome
        ? 'max-w-2xl text-lg leading-relaxed text-brand sm:text-2xl'
        : 'max-w-2xl text-base leading-relaxed text-brand sm:text-lg';

    $hasAction = isset($action) ? trim($action) !== '' : false;
    $hasLeadSlot = isset($leadSlot) ? trim($leadSlot) !== '' : false;
@endphp

<div {{ $attributes->class(['ds-hero flex w-full flex-col items-center gap-10']) }}>
    {{-- Bloc texte --}}
    <div class="flex w-full flex-col gap-6 px-0 sm:px-10">
        @if ($title || $accent)
            <div class="flex flex-col gap-1">
                @if ($title)
                    <h1 class="{{ $titleClass }}">{{ $title }}</h1>
                @endif
                @if ($accent)
                    <p class="{{ $accentClass }}">{{ $accent }}</p>
                @endif
            </div>
        @endif

        @if ($hasLeadSlot)
            <div class="{{ $leadClass }}">
                {{ $leadSlot }}
            </div>
        @elseif ($lead)
            <p class="{{ $leadClass }}">{{ $lead }}</p>
        @endif
    </div>

    {{-- Panneau d'action attaché (barre de recherche, CTA, etc.) --}}
    @if ($hasAction)
        <div class="w-full rounded-t-2xl bg-brand p-6 shadow-[0_4px_8px_0_rgba(27,62,61,0.04)] sm:p-10">
            {{ $action }}
        </div>
    @endif
</div>

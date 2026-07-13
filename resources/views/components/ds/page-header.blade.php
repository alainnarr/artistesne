{{--
    DS · Page header — en-tête léger pour les pages internes.

    Utilisé sur les pages secondaires (Liste artistes, Fiche artiste,
    Connexion artiste, etc.) qui n'ont pas besoin du grand hero d'accueil.

    Comprend :
        - Lien de retour optionnel (slot 'back')
        - Fil d'Ariane optionnel (slot 'breadcrumbs')
        - Titre serif (Lora Bold) avec accent italique teal optionnel
        - Paragraphe descriptif optionnel
        - Slot par défaut pour actions (boutons, filtres, etc.)

    Props :
        $title       : titre principal
        $accent      : fragment italique teal (string|null) — affiché
                       inline après le titre (ex: "À propos de <em>Artiste.ne</em>")
        $lead        : paragraphe descriptif (string|null)

    Slots :
        $breadcrumbs : fil d'Ariane (au-dessus du titre)
        $back        : lien de retour (au-dessus du titre, si pas de breadcrumb)
        $leadSlot    : paragraphe descriptif HTML (au lieu de $lead)
        default      : actions / extras sous le titre
--}}
@props([
    'title'  => null,
    'accent' => null,
    'lead'   => null,
])

@php
    $hasBack = isset($back) ? trim($back) !== '' : false;
    $hasBreadcrumbs = isset($breadcrumbs) ? trim($breadcrumbs) !== '' : false;
    $hasLeadSlot = isset($leadSlot) ? trim($leadSlot) !== '' : false;
    $hasActions = trim($slot) !== '';
@endphp

<header {{ $attributes->class(['ds-page-header flex w-full flex-col gap-6']) }}>
    @if ($hasBreadcrumbs)
        <nav class="text-sm text-brand-muted" aria-label="Fil d'Ariane">
            {{ $breadcrumbs }}
        </nav>
    @elseif ($hasBack)
        <div class="text-sm">
            {{ $back }}
        </div>
    @endif

    @if ($title || $accent)
        <div class="flex flex-col gap-3">
            <h1 class="font-serif text-3xl font-bold leading-tight text-brand sm:text-4xl lg:text-[40px] lg:leading-[48px]">
                {{ $title }}
                @if ($accent)
                    <span class="font-serif font-semibold italic text-brand-teal">{{ $accent }}</span>
                @endif
            </h1>

            @if ($hasLeadSlot)
                <div class="max-w-3xl text-base leading-relaxed text-brand sm:text-lg">
                    {{ $leadSlot }}
                </div>
            @elseif ($lead)
                <p class="max-w-3xl text-base leading-relaxed text-brand sm:text-lg">{{ $lead }}</p>
            @endif
        </div>
    @endif

    @if ($hasActions)
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            {{ $slot }}
        </div>
    @endif
</header>

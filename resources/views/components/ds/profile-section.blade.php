{{--
    DS · Profile section — Figma 561:48719 / 561:48727 / 561:48734 / 561:48739

    Bloc à deux colonnes utilisé sur la fiche artiste :
      - Colonne gauche : titre h2 serif
      - Colonne droite : contenu (liste de liens, tags, texte, etc.)
      - Hairline en bas (sauf si $divided=false)

    Sur mobile : empilé verticalement.

    Props :
        $title    — titre h2 (string)
        $divided  — affiche la hairline en bas (bool, défaut true)
--}}
@props([
    'title',
    'divided' => true,
])

<div {{ $attributes->class([
    'ds-profile-section grid grid-cols-1 gap-6 py-10 sm:grid-cols-[260px_1fr] sm:gap-16',
    'border-b border-brand-hairline' => $divided,
]) }}>
    <h2 class="font-serif text-2xl font-bold leading-tight text-brand">
        {{ $title }}
    </h2>
    <div class="flex flex-col gap-3">
        {{ $slot }}
    </div>
</div>

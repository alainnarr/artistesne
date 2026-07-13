{{--
    DS · Banner Title — Figma node 2439:38826
    https://www.figma.com/design/quwzQh6G272IDJtnfF7teN/?node-id=2439-38826

    Bannière de tête de page : titre Lora bold + sous-titre italique brand-teal +
    description optionnelle + (slot) search.

    Props :
        $title       — première ligne du titre
        $subtitle    — seconde ligne (rendue en Lora italic teal)
        $description — paragraphe sous le titre
        $compact     — version condensée (mobile / petit hero)
--}}
@props([
    'title',
    'subtitle'    => null,
    'description' => null,
    'compact'     => false,
])

@php
    $titleClass = $compact
        ? 'font-serif text-2xl sm:text-3xl font-bold leading-tight text-brand'
        : 'font-serif text-3xl sm:text-4xl font-bold leading-tight text-brand';
@endphp

<section {{ $attributes->class('ds-banner-title w-full bg-brand text-brand-paper') }}>
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8 lg:py-16">

        <h1 class="{{ $titleClass }} text-brand-paper">
            {{ $title }}
            @if ($subtitle)
                <span class="block italic text-brand-teal-light">{{ $subtitle }}</span>
            @endif
        </h1>

        @if ($description)
            <p class="mt-4 max-w-2xl text-base text-brand-paper/90">{{ $description }}</p>
        @endif

        @if ($slot->isNotEmpty())
            <div class="mt-6">
                {{ $slot }}
            </div>
        @endif
    </div>
</section>

{{--
    DS · Banner Push — Figma node 2439:39402
    https://www.figma.com/design/quwzQh6G272IDJtnfF7teN/?node-id=2439-39402

    Bannière incitative : titre + description + CTA, fond brand sombre.

    Layouts :
      - 'large'    : full page, CTA à droite
      - 'medium'   : version intermédiaire
      - 'small'    : version compacte verticale
      - 'tablet'   : centré pour breakpoint tablette
      - 'mobile'   : empilé pour mobile

    Props :
        $title
        $description
        $ctaLabel
        $ctaHref
        $layout = 'large' | 'medium' | 'small'
--}}
@props([
    'title',
    'description' => null,
    'ctaLabel'    => 'En savoir plus',
    'ctaHref'     => '#',
    'layout'      => 'large',
])

@php
    $vertical = in_array($layout, ['small', 'mobile'], true);
    $padding = match ($layout) {
        'small'  => 'p-6',
        'medium' => 'p-8',
        default  => 'p-10',
    };
    $titleClass = match ($layout) {
        'small'  => 'font-serif text-xl font-bold',
        'medium' => 'font-serif text-2xl font-bold',
        default  => 'font-serif text-2xl sm:text-3xl font-bold',
    };
@endphp

<aside {{ $attributes->class('ds-banner-push w-full bg-brand text-brand-paper '.$padding) }}>
    <div class="flex w-full {{ $vertical ? 'flex-col gap-5' : 'flex-col items-start gap-6 lg:flex-row lg:items-center lg:justify-between' }}">

        <div class="{{ $vertical ? 'w-full' : 'max-w-2xl' }}">
            <h2 class="{{ $titleClass }} leading-tight text-brand-paper">{{ $title }}</h2>
            @if ($description)
                <p class="mt-3 text-base text-brand-paper/90">{{ $description }}</p>
            @endif
        </div>

        <div class="shrink-0">
            <x-ds.btn :href="$ctaHref" variant="primary" size="md" icon-trailing="arrow-right">
                {{ $ctaLabel }}
            </x-ds.btn>
        </div>
    </div>
</aside>

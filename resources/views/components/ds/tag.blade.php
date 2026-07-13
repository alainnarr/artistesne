{{--
    DS · Tag — Figma node 2274:11691
    https://www.figma.com/design/quwzQh6G272IDJtnfF7teN/?node-id=2274-11691

    Deux variantes :
      - 'primary'   : tag activité / mots-clés  (BG mint, texte brand)
      - 'secondary' : tag neutre (BG #f7f7f7, border #e9eae9, texte muted)
      - 'domain'    : tag domaine artistique — carré couleur + label majuscule
                     (sous-variants 'primary' / 'secondary' via $domainTone)

    Props :
        $variant     = 'primary' | 'secondary' | 'domain'
        $domainTone  = 'primary' | 'secondary'   (uniquement si variant=domain)
        $removable   = true affiche une croix de suppression
        $href        = rend comme <a>
--}}
@props([
    'variant'    => 'primary',
    'domainTone' => 'primary',
    'removable'  => false,
    'href'       => null,
])

@php
    if ($variant === 'domain') {
        // Domain tag : carré couleur + label MAJ tracking large
        $squareColor = $domainTone === 'secondary' ? 'bg-domain-secondary' : 'bg-domain-primary';
        $classes = 'inline-flex items-center gap-2 pl-1 pr-3 text-xs font-normal uppercase tracking-[0.135em] text-brand bg-transparent';
    } elseif ($variant === 'secondary') {
        $classes = 'inline-flex items-center gap-1.5 h-7 px-3 text-xs font-medium text-brand-muted bg-tag-secondary-bg border border-tag-secondary-border';
    } else {
        // primary
        $classes = 'inline-flex items-center gap-1.5 h-7 px-3 text-xs font-medium text-brand bg-brand-mint';
    }

    $tag = $href ? 'a' : 'span';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->class($classes) }}>
    @if ($variant === 'domain')
        <span class="inline-block size-3 shrink-0 {{ $squareColor }}"></span>
    @endif
    <span>{{ $slot }}</span>
    @if ($removable)
        <button type="button" class="ml-1 -mr-1 inline-flex shrink-0 items-center justify-center text-current/70 hover:text-current">
            <x-picto name="close" class="size-3" />
        </button>
    @endif
</{{ $tag }}>

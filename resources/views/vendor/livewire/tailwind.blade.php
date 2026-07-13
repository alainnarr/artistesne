@php
    $scrollIntoViewJsSnippet = "(\$el.closest('body') || document.querySelector('body')).scrollIntoView()";
@endphp

{{--
    Pagination DS — surcharge du thème "tailwind" de Livewire.

    Aligne le composant sur la charte Artistes.ne (angles francs, tokens
    brand-*, picto flèches) et traduit le texte par défaut ("Showing X to Y
    of Z results", "Previous/Next").
--}}
<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Navigation de pagination" class="flex items-center justify-center gap-2">

            {{-- Lien précédent --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex size-10 items-center justify-center border border-brand-hairline text-brand-muted/40 cursor-not-allowed" aria-hidden="true">
                    <x-picto name="arrow-left" class="size-4" />
                </span>
            @else
                <button type="button"
                        wire:click="previousPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        wire:loading.attr="disabled"
                        class="inline-flex size-10 items-center justify-center border border-brand-hairline text-brand transition-colors hover:bg-brand-cream"
                        aria-label="Page précédente">
                    <x-picto name="arrow-left" class="size-4" />
                </button>
            @endif

            {{-- Numéros de page --}}
            <div class="flex items-center gap-2">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="inline-flex size-10 items-center justify-center text-sm text-brand-muted" aria-hidden="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="inline-flex size-10 items-center justify-center border border-brand bg-brand text-sm font-medium text-brand-paper">{{ $page }}</span>
                                @else
                                    <button type="button"
                                            wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                            aria-label="Aller à la page {{ $page }}"
                                            class="inline-flex size-10 items-center justify-center border border-brand-hairline text-sm text-brand transition-colors hover:bg-brand-cream">{{ $page }}</button>
                                @endif
                            </span>
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Lien suivant --}}
            @if ($paginator->hasMorePages())
                <button type="button"
                        wire:click="nextPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        wire:loading.attr="disabled"
                        class="inline-flex size-10 items-center justify-center border border-brand-hairline text-brand transition-colors hover:bg-brand-cream"
                        aria-label="Page suivante">
                    <x-picto name="arrow-right" class="size-4" />
                </button>
            @else
                <span class="inline-flex size-10 items-center justify-center border border-brand-hairline text-brand-muted/40 cursor-not-allowed" aria-hidden="true">
                    <x-picto name="arrow-right" class="size-4" />
                </span>
            @endif
        </nav>
    @endif
</div>

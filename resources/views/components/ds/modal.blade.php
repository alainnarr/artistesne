{{--
    DS · Modal — Figma node 2275:11730
    https://www.figma.com/design/quwzQh6G272IDJtnfF7teN/?node-id=2275-11730

    Anatomie : backdrop · container · header · body · footer.

    Pilote via Alpine : <x-ds.modal id="filters" title="Filtres">…</x-ds.modal>
    Pour ouvrir : <button @click="$dispatch('open-modal', { id: 'filters' })">…</button>

    Props :
        $id       — identifiant unique (string)
        $title    — titre dans le header
        $size     — 'sm' | 'md' (défaut) | 'lg'
        $footer   — slot nommé : <x-slot:footer>…</x-slot:footer>
--}}
@props([
    'id',
    'title' => null,
    'size'  => 'md',
])

@php
    $widthClass = match ($size) {
        'sm' => 'max-w-sm',
        'lg' => 'max-w-2xl',
        default => 'max-w-md',
    };
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail?.id === @js($id)) open = true"
    x-on:close-modal.window="if (! $event.detail?.id || $event.detail.id === @js($id)) open = false"
    x-on:keydown.escape.window="open = false"
>
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">

            {{-- Backdrop --}}
            <div
                x-show="open"
                x-transition.opacity
                @click="open = false"
                class="absolute inset-0 bg-neutral-30/70"
            ></div>

            {{-- Container --}}
            <div
                x-show="open"
                x-transition
                class="relative z-10 w-full {{ $widthClass }} bg-brand-paper shadow-2xl"
                role="dialog"
                aria-modal="true"
                @if($title) aria-labelledby="modal-{{ $id }}-title" @endif
            >
                {{-- Header --}}
                @if ($title)
                    <header class="flex items-center justify-between border-b border-brand-hairline px-5 py-4">
                        <h2 id="modal-{{ $id }}-title" class="font-serif text-xl font-bold text-brand">{{ $title }}</h2>
                        <button type="button" @click="open = false" class="text-brand-muted hover:text-brand" aria-label="Fermer">
                            <x-picto name="close" class="size-5" />
                        </button>
                    </header>
                @endif

                {{-- Body --}}
                <div {{ $attributes->class('max-h-[70vh] overflow-y-auto px-5 py-5') }}>
                    {{ $slot }}
                </div>

                {{-- Footer --}}
                @isset($footer)
                    <footer class="flex items-center justify-between gap-3 border-t border-brand-hairline px-5 py-4">
                        {{ $footer }}
                    </footer>
                @endisset
            </div>
        </div>
    </template>
</div>

{{--
    DS · Photo Upload — portrait upload with Cropper.js crop modal.

    Requires the `imageCropper` Alpine data component (registered in app.js).
    Sends the cropped JPEG to Livewire via `$wire.upload('photo', file)`.

    Props :
        $currentImageUrl = string|null — existing portrait URL (displayed as preview)
        $uploading       = bool         — passed from Livewire wire:loading state
        $error           = string|null  — validation error message
--}}
@props([
    'currentImageUrl' => null,
    'uploading'       => false,
    'error'           => null,
])

<div x-data="imageCropper" class="flex flex-col gap-4">

    {{-- Current / preview portrait --}}
    <div class="aspect-[4/5] w-full max-w-[200px] overflow-hidden bg-brand-hairline">
        @if ($currentImageUrl)
            <img
                src="{{ $currentImageUrl }}"
                alt="Portrait actuel"
                class="size-full object-cover grayscale"
                x-show="!hasPreview"
            />
        @endif
        <div class="flex h-full items-center justify-center text-brand-muted/40 font-serif text-5xl"
             x-show="!hasPreview && !@js((bool) $currentImageUrl)">
            <x-picto name="camera" class="size-10 text-brand-muted/40" />
        </div>
    </div>

    {{-- File picker --}}
    <label class="inline-flex cursor-pointer items-center gap-2 border border-brand-muted px-4 py-2 text-sm font-medium text-brand transition hover:bg-brand-cream">
        <x-picto name="camera" class="size-4" />
        <span x-text="hasPreview ? 'Changer la photo' : '{{ $currentImageUrl ? 'Remplacer la photo' : 'Ajouter une photo' }}'">
            {{ $currentImageUrl ? 'Remplacer la photo' : 'Ajouter une photo' }}
        </span>
        <input
            type="file"
            accept="image/jpeg,image/png"
            class="sr-only"
            @change="handleFileSelect($event)"
        />
    </label>

    <p class="text-xs text-brand-muted">JPG ou PNG · max. 5 Mo · min. 400×500 px · noir et blanc automatique</p>

    @if ($error)
        <p class="text-xs text-red-600">{{ $error }}</p>
    @endif

    {{-- Crop modal — shown when a file has been selected --}}
    <div
        x-show="hasPreview"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/70 p-4"
    >
        <div class="flex w-full max-w-lg flex-col gap-4 bg-brand-paper p-6">
            <div class="flex items-center justify-between">
                <h3 class="font-serif text-lg font-bold text-brand">Cadrer la photo</h3>
                <button type="button" @click="cancelCrop" class="text-brand-muted hover:text-brand" aria-label="Annuler">
                    <x-picto name="close" class="size-5" />
                </button>
            </div>

            {{-- Cropper target --}}
            <div class="max-h-[50vh] w-full overflow-hidden">
                <img x-ref="cropperImage" :src="previewSrc" alt="Aperçu" class="block max-w-full" />
            </div>

            <p class="text-xs text-brand-muted">Déplacez et redimensionnez la zone pour cadrer votre portrait (ratio 4:5).</p>

            <div class="flex gap-3">
                <button type="button" @click="cancelCrop"
                        class="flex-1 border border-brand-muted py-3 text-sm font-medium text-brand hover:bg-brand-cream transition">
                    Annuler
                </button>
                <button type="button" @click="confirmCrop" :disabled="uploading"
                        class="flex-1 bg-brand-mint py-3 text-sm font-semibold text-brand hover:bg-brand-mint-hover transition disabled:opacity-50">
                    <span x-show="!uploading">Valider le recadrage</span>
                    <span x-show="uploading" x-cloak>Téléversement…</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Uploaded feedback --}}
    <div wire:loading wire:target="photo" class="text-sm text-brand-muted">
        Traitement de l'image…
    </div>
</div>

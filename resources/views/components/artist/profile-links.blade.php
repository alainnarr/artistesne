{{--
    Shared "Mes liens personnels" + "Liens vers mes collaborations" + contact
    toggle section for the artist profile forms (ProfileSetup step 2 +
    EditProfile) — Figma node 561:50618.

    The consuming Livewire component must declare $links, $collaborations,
    $display_contact_button and the ManagesArtistProfileFields trait's
    add/remove methods.
--}}
@props([
    'links' => [],
    'collaborations' => [],
])

<div class="flex flex-col gap-10">
    {{-- Mes liens personnels --}}
    <section class="flex flex-col gap-5">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-2xl font-bold text-brand">Mes liens personnels</h2>
            @if (count($links) < 6)
                <x-ds.btn type="button" variant="secondary" size="sm" icon="plus" wire:click="addLink">
                    Ajouter
                </x-ds.btn>
            @endif
        </div>
        <p class="text-sm text-brand-muted">Liens vers mes réseaux et sites personnels.</p>
        <div class="flex flex-col gap-4">
            @forelse ($links as $index => $link)
                <div class="flex flex-col gap-3 border border-brand-hairline p-4 sm:flex-row sm:items-end" wire:key="pl-link-{{ $index }}">
                    <div class="w-full sm:w-32">
                        <x-ds.field wire:model="links.{{ $index }}.label" label="Libellé" />
                    </div>
                    <div class="flex-1">
                        <x-ds.field wire:model="links.{{ $index }}.url" label="URL" />
                    </div>
                    <x-ds.btn type="button" variant="secondary" size="sm" icon="close" wire:click="removeLink({{ $index }})">Retirer</x-ds.btn>
                </div>
            @empty
                <p class="text-base text-brand-muted">Aucun lien renseigné.</p>
            @endforelse
        </div>
    </section>

    {{-- Liens vers mes collaborations --}}
    <section class="flex flex-col gap-5">
        <div class="flex items-center justify-between">
            <h2 class="font-serif text-2xl font-bold text-brand">Liens vers mes collaborations</h2>
            <x-ds.btn type="button" variant="secondary" size="sm" icon="plus" wire:click="addCollaboration">
                Ajouter
            </x-ds.btn>
        </div>
        <p class="text-sm text-brand-muted">Liens vers vos collaborations.</p>
        <div class="flex flex-col gap-4">
            @forelse ($collaborations as $index => $collab)
                <div class="flex flex-col gap-3 border border-brand-hairline p-4 sm:flex-row sm:items-end" wire:key="pl-collab-{{ $index }}">
                    <div class="flex-1">
                        <x-ds.field wire:model="collaborations.{{ $index }}.name" label="Nom" />
                    </div>
                    <div class="flex-1">
                        <x-ds.field wire:model="collaborations.{{ $index }}.url" label="URL (facultatif)" />
                    </div>
                    <x-ds.btn type="button" variant="secondary" size="sm" icon="close" wire:click="removeCollaboration({{ $index }})">Retirer</x-ds.btn>
                </div>
            @empty
                <p class="text-base text-brand-muted">Aucune collaboration renseignée.</p>
            @endforelse
        </div>
    </section>

    {{-- Contact --}}
    <section class="flex flex-col gap-3">
        <div class="flex items-start gap-3 border border-brand-hairline p-4">
            <input type="checkbox" id="display_contact_button" wire:model="display_contact_button" class="mt-0.5 size-4 accent-brand-teal" />
            <label for="display_contact_button" class="flex flex-col gap-1 text-sm">
                <span class="font-medium text-brand">Afficher le bouton « Me contacter »</span>
                <span class="text-brand-muted">Votre adresse e-mail sera visible sous forme de lien de contact sur votre profil public.</span>
            </label>
        </div>
    </section>
</div>

{{--
    Shared "Mon profil artiste" section for the artist profile forms
    (ProfileSetup step 1 + EditProfile) — Figma node 561:50618.

    Renders: section header/description, photo upload, "Domaines et
    activités" subheader (domaine principal, activités principales,
    domaine secondaire, activités secondaires, mots-clés) and the
    "Description de mon activité" textarea.

    The consuming Livewire component must declare the properties bound
    below ($discipline_main_id, $activities, $secondary_activities,
    $keywords, $newActivity, $newSecondaryActivity, $newKeyword, $biography,
    $photo) and the ManagesArtistProfileFields trait's add/remove methods.
--}}
@props([
    'disciplineOptions' => [],
    'mainActivityOptions' => [],
    'secondaryActivityOptions' => [],
    'currentImageUrl' => null,
    'discipline_main_id' => null,
    'activities' => [],
    'secondary_activities' => [],
    'keywords' => [],
])

<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-2">
        <h2 class="font-serif text-2xl font-bold text-brand">Mon profil artiste</h2>
        <p class="text-sm text-brand-muted">
            Ces informations constituent votre fiche publique visible sur l'annuaire.
        </p>
    </div>

    {{-- Photo de profil --}}
    <div class="flex flex-col gap-1">
        <label class="block text-sm font-medium text-brand">Photo de profil</label>
        <x-ds.photo-upload
            :current-image-url="$currentImageUrl"
            :error="$errors->first('photo')"
        />
    </div>

    <h3 class="font-serif text-lg font-bold text-brand">Domaines et activités</h3>

    <x-ds.field
        as="select"
        wire:model.live="discipline_main_id"
        label="Domaine principal"
    >
        @foreach ($disciplineOptions as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </x-ds.field>

    {{-- Activités principales --}}
    <div class="flex flex-col gap-3">
        <label class="block text-sm font-medium text-brand">
            Activités principales <span class="font-normal text-brand-muted">(max. 4)</span>
        </label>
        @if (count($activities) > 0)
            <div class="flex flex-wrap gap-2">
                @foreach ($activities as $i => $activity)
                    <span class="inline-flex items-center gap-1.5 border border-brand-muted bg-brand-paper px-3 py-1 text-sm text-brand"
                          wire:key="pf-act-{{ $i }}">
                        {{ $activity }}
                        <button type="button" wire:click="removeActivity({{ $i }})" class="text-brand-muted hover:text-brand" aria-label="Retirer">
                            <x-picto name="close" class="size-3.5" />
                        </button>
                    </span>
                @endforeach
            </div>
        @endif
        @if (count($activities) < 4)
            <div class="flex gap-2">
                <x-ds.field
                    as="select"
                    wire:model="newActivity"
                    label="Ajouter une activité"
                    class="flex-1"
                    :disabled="blank($discipline_main_id)"
                >
                    @foreach ($mainActivityOptions as $label)
                        <option value="{{ $label }}">{{ $label }}</option>
                    @endforeach
                </x-ds.field>
                <x-ds.btn type="button" variant="secondary" size="sm" wire:click="addActivity">Ajouter</x-ds.btn>
            </div>
        @endif
        @error('activities') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <x-ds.field
        as="select"
        wire:model.live="discipline_secondary"
        label="Domaine secondaire (facultatif)"
    >
        @foreach ($disciplineOptions as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </x-ds.field>

    {{-- Activités secondaires --}}
    <div class="flex flex-col gap-3">
        <label class="block text-sm font-medium text-brand">Activités secondaires</label>
        @if (count($secondary_activities) > 0)
            <div class="flex flex-wrap gap-2">
                @foreach ($secondary_activities as $i => $activity)
                    <span class="inline-flex items-center gap-1.5 border border-brand-hairline bg-brand-paper px-3 py-1 text-sm text-brand"
                          wire:key="pf-sec-act-{{ $i }}">
                        {{ $activity }}
                        <button type="button" wire:click="removeSecondaryActivity({{ $i }})" class="text-brand-muted hover:text-brand" aria-label="Retirer">
                            <x-picto name="close" class="size-3.5" />
                        </button>
                    </span>
                @endforeach
            </div>
        @endif
        <div class="flex gap-2">
            <x-ds.field as="select" wire:model="newSecondaryActivity" label="Ajouter une activité secondaire" class="flex-1">
                @foreach ($secondaryActivityOptions as $label)
                    <option value="{{ $label }}">{{ $label }}</option>
                @endforeach
            </x-ds.field>
            <x-ds.btn type="button" variant="secondary" size="sm" wire:click="addSecondaryActivity">Ajouter</x-ds.btn>
        </div>
        @error('secondary_activities') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    {{-- Mots-clés --}}
    <div class="flex flex-col gap-3">
        <label class="block text-sm font-medium text-brand">Mots-clés</label>
        @if (count($keywords) > 0)
            <div class="flex flex-wrap gap-2">
                @foreach ($keywords as $i => $keyword)
                    <span class="inline-flex items-center gap-1.5 border border-brand-hairline bg-brand-paper px-3 py-1 text-sm text-brand"
                          wire:key="pf-kw-{{ $i }}">
                        {{ $keyword }}
                        <button type="button" wire:click="removeKeyword({{ $i }})" class="text-brand-muted hover:text-brand" aria-label="Retirer">
                            <x-picto name="close" class="size-3.5" />
                        </button>
                    </span>
                @endforeach
            </div>
        @endif
        <div class="flex gap-2">
            <x-ds.field
                wire:model="newKeyword"
                label="Ajouter un mot-clé"
                wire:keydown.enter.prevent="addKeyword"
                class="flex-1"
            />
            <x-ds.btn type="button" variant="secondary" size="sm" wire:click="addKeyword">Ajouter</x-ds.btn>
        </div>
    </div>

    <x-ds.field
        as="textarea"
        wire:model="biography"
        label="Description de mon activité"
        required
        :rows="6"
        description="Présentez votre parcours, votre démarche et vos projets."
    />
</div>

<div>
    <x-ds.section variant="paper">
        <x-ds.hero variant="page" title="Modifier" accent="ma page">
            <x-slot:lead-slot>
                <p>
                    Toutes les modifications sont revues par l'administration avant publication.
                    Vous pouvez à tout moment annuler vos changements.
                </p>
            </x-slot:lead-slot>
        </x-ds.hero>
    </x-ds.section>

    <x-ds.section variant="cream">
        <div class="mx-auto w-full max-w-[928px] bg-brand-paper p-6 sm:p-12">
            @if ($submitted)
                <div class="mb-8 flex items-start gap-4 border-l-4 border-brand-mint bg-brand-cream/60 p-5">
                    <span class="flex size-10 shrink-0 items-center justify-center bg-brand-mint text-brand">
                        <x-picto name="check" class="size-5" />
                    </span>
                    <div>
                        <h2 class="font-serif text-lg font-bold text-brand">Modification envoyée</h2>
                        <p class="mt-1 text-base text-brand-muted">
                            Votre proposition a été transmise. Vous serez notifié·e dès qu'elle sera traitée.
                        </p>
                    </div>
                </div>
            @endif

            @if ($hasPendingChange && ! $submitted)
                <div class="mb-8 flex items-start gap-4 border-l-4 border-brand-teal bg-brand-cream/60 p-5">
                    <div>
                        <h2 class="font-serif text-lg font-bold text-brand">Une modification est déjà en attente</h2>
                        <p class="mt-1 text-base text-brand-muted">
                            Vous ne pouvez pas soumettre de nouvelle modification tant que la précédente n'a pas été traitée.
                        </p>
                    </div>
                </div>
            @endif

            <form wire:submit="save" class="register-form flex flex-col gap-10">
                {{-- Identité --}}
                <section class="flex flex-col gap-5">
                    <h2 class="font-serif text-2xl font-bold text-brand">Identité</h2>
                    <x-ds.input
                        wire:model="artist_name"
                        label="Nom d'artiste"
                        name="artist_name"
                        :error="$errors->first('artist_name')"
                    />
                    <x-ds.select
                        wire:model.live="discipline_main_id"
                        name="discipline_main_id"
                        label="Domaine principal"
                        :error="$errors->first('discipline_main_id')"
                    >
                        <option value="">— Choisir un domaine —</option>
                        @foreach ($disciplineOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-ds.select>
                    <x-ds.input
                        wire:model="city"
                        label="Ville / Commune"
                        name="city"
                        placeholder="Ex: Neuchâtel"
                        :error="$errors->first('city')"
                    />
                    <x-ds.select
                        wire:model.live="discipline_secondary"
                        name="discipline_secondary"
                        label="Domaine secondaire (facultatif)"
                        :error="$errors->first('discipline_secondary')"
                    >
                        <option value="">— Aucun —</option>
                        @foreach ($disciplineOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-ds.select>

                    {{-- Activités principales --}}
                    <div class="flex flex-col gap-3">
                        <label class="block text-sm font-medium text-brand">
                            Activités principales <span class="font-normal text-brand-muted">(max. 4)</span>
                        </label>
                        @if (count($activities) > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach ($activities as $i => $activity)
                                    <span class="inline-flex items-center gap-1.5 border border-brand-muted bg-brand-paper px-3 py-1 text-sm text-brand"
                                          wire:key="ep-act-{{ $i }}">
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
                                <x-ds.select
                                    wire:model="newActivity"
                                    name="newActivity"
                                    class="flex-1"
                                    :disabled="blank($discipline_main_id)"
                                >
                                    <option value="">
                                        {{ blank($discipline_main_id) ? '— Choisir d\'abord un domaine —' : '— Choisir une activité —' }}
                                    </option>
                                    @foreach ($mainActivityOptions as $label)
                                        <option value="{{ $label }}">{{ $label }}</option>
                                    @endforeach
                                </x-ds.select>
                                <x-ds.btn type="button" variant="secondary" size="sm" wire:click="addActivity">Ajouter</x-ds.btn>
                            </div>
                        @endif
                        @error('activities') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Activités secondaires --}}
                    <div class="flex flex-col gap-3">
                        <label class="block text-sm font-medium text-brand">Activités secondaires</label>
                        @if (count($secondary_activities) > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach ($secondary_activities as $i => $activity)
                                    <span class="inline-flex items-center gap-1.5 border border-brand-hairline bg-brand-paper px-3 py-1 text-sm text-brand"
                                          wire:key="ep-sec-act-{{ $i }}">
                                        {{ $activity }}
                                        <button type="button" wire:click="removeSecondaryActivity({{ $i }})" class="text-brand-muted hover:text-brand" aria-label="Retirer">
                                            <x-picto name="close" class="size-3.5" />
                                        </button>
                                    </span>
                                @endforeach
                            </div>
                        @endif
                        <div class="flex gap-2">
                            <x-ds.select wire:model="newSecondaryActivity" name="newSecondaryActivity" class="flex-1">
                                <option value="">— Choisir une activité —</option>
                                @foreach ($secondaryActivityOptions as $label)
                                    <option value="{{ $label }}">{{ $label }}</option>
                                @endforeach
                            </x-ds.select>
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
                                          wire:key="ep-kw-{{ $i }}">
                                        {{ $keyword }}
                                        <button type="button" wire:click="removeKeyword({{ $i }})" class="text-brand-muted hover:text-brand" aria-label="Retirer">
                                            <x-picto name="close" class="size-3.5" />
                                        </button>
                                    </span>
                                @endforeach
                            </div>
                        @endif
                        <div class="flex gap-2">
                            <x-ds.input wire:model="newKeyword" name="newKeyword" placeholder="Ex. : paysage…" wire:keydown.enter.prevent="addKeyword" class="flex-1" />
                            <x-ds.btn type="button" variant="secondary" size="sm" wire:click="addKeyword">Ajouter</x-ds.btn>
                        </div>
                    </div>
                </section>

                {{-- Biographie --}}
                <section class="flex flex-col gap-5">
                    <h2 class="font-serif text-2xl font-bold text-brand">Photo de profil</h2>
                    <x-ds.photo-upload
                        :current-image-url="$currentImageUrl"
                        :error="$errors->first('photo')"
                    />
                </section>

                {{-- Biographie --}}
                <section class="flex flex-col gap-5">
                    <h2 class="font-serif text-2xl font-bold text-brand">Biographie</h2>
                    <x-ds.textarea
                        wire:model="biographyText"
                        name="biographyText"
                        :rows="10"
                        description="Séparez vos paragraphes par une ligne vide."
                        :error="$errors->first('biographyText')"
                    />
                </section>

                {{-- Liens personnels --}}
                <section class="flex flex-col gap-5">
                    <div class="flex items-center justify-between">
                        <h2 class="font-serif text-2xl font-bold text-brand">Espaces personnels</h2>
                        @if (count($links) < 6)
                            <x-ds.btn type="button" variant="secondary" size="sm" icon="plus" wire:click="addLink">
                                Ajouter
                            </x-ds.btn>
                        @endif
                    </div>
                    <div class="flex flex-col gap-4">
                        @forelse ($links as $index => $link)
                            <div class="flex flex-col gap-3 border border-brand-hairline p-4 sm:flex-row sm:items-end" wire:key="link-{{ $index }}">
                                <div class="w-full sm:w-32">
                                    <x-ds.input wire:model="links.{{ $index }}.label" label="Libellé" :error="$errors->first('links.'.$index.'.label')" />
                                </div>
                                <div class="flex-1">
                                    <x-ds.input wire:model="links.{{ $index }}.url" label="URL" :error="$errors->first('links.'.$index.'.url')" />
                                </div>
                                <x-ds.btn type="button" variant="secondary" size="sm" icon="close" wire:click="removeLink({{ $index }})">Retirer</x-ds.btn>
                            </div>
                        @empty
                            <p class="text-base text-brand-muted">Aucun lien renseigné.</p>
                        @endforelse
                    </div>
                </section>

                {{-- Collaborations --}}
                <section class="flex flex-col gap-5">
                    <div class="flex items-center justify-between">
                        <h2 class="font-serif text-2xl font-bold text-brand">Collaborations</h2>
                        <x-ds.btn type="button" variant="secondary" size="sm" icon="plus" wire:click="addCollaboration">
                            Ajouter
                        </x-ds.btn>
                    </div>
                    <div class="flex flex-col gap-4">
                        @forelse ($collaborations as $index => $collab)
                            <div class="flex flex-col gap-3 border border-brand-hairline p-4 sm:flex-row sm:items-end" wire:key="collab-{{ $index }}">
                                <div class="flex-1">
                                    <x-ds.input wire:model="collaborations.{{ $index }}.name" label="Nom" :error="$errors->first('collaborations.'.$index.'.name')" />
                                </div>
                                <div class="flex-1">
                                    <x-ds.input wire:model="collaborations.{{ $index }}.url" label="URL (facultatif)" :error="$errors->first('collaborations.'.$index.'.url')" />
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
                        <input type="checkbox" id="ep_display_contact_button" wire:model="display_contact_button" class="mt-0.5 size-4 accent-brand-teal" />
                        <label for="ep_display_contact_button" class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-brand">Afficher le bouton « Me contacter »</span>
                            <span class="text-brand-muted">Votre adresse e-mail sera visible sous forme de lien de contact sur votre profil public.</span>
                        </label>
                    </div>
                </section>

                <div class="flex flex-col items-stretch gap-3 border-t border-brand-hairline pt-6 sm:flex-row sm:items-center sm:justify-end">
                    <x-ds.btn variant="secondary" size="md" :href="route('artist.dashboard')" wire:navigate>
                        Annuler
                    </x-ds.btn>
                    <x-ds.btn type="submit" variant="primary" size="md" icon-trailing="arrow-right" :disabled="$hasPendingChange">
                        Soumettre la modification
                    </x-ds.btn>
                </div>
            </form>
        </div>
    </x-ds.section>
</div>

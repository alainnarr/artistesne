<div>
    <x-ds.section variant="paper">
        <x-ds.hero variant="page" title="Créez" accent="votre profil">
            <x-slot:lead-slot>
                <p>Votre demande de référencement a été validée. Complétez votre profil public en quelques étapes.</p>
            </x-slot:lead-slot>
        </x-ds.hero>
    </x-ds.section>

    <x-ds.section variant="cream">
        <div class="mx-auto w-full max-w-[928px] bg-brand-paper px-6 py-12 sm:px-16 sm:py-16">

            @if ($submitted)
                {{-- ===================== CONFIRMATION ===================== --}}
                <div class="flex flex-col items-center gap-6 py-8 text-center">
                    <div class="flex size-16 items-center justify-center rounded-full bg-brand-mint">
                        <x-picto name="check" class="size-8 text-brand" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <h2 class="font-serif text-2xl font-bold text-brand">Profil enregistré</h2>
                        <p class="text-brand-muted">Votre profil a été transmis à l'équipe du SCNE pour validation et publication.</p>
                    </div>
                    <x-ds.btn :href="route('artist.dashboard')" variant="primary" size="md">
                        Retour au tableau de bord
                    </x-ds.btn>
                </div>
            @else
                <div class="register-form mx-auto flex w-full max-w-[500px] flex-col gap-8">

                    {{-- Read-only confirmation --}}
                    <div class="flex flex-col gap-1 rounded border border-brand-hairline bg-brand-cream px-4 py-3 text-sm">
                        <span class="font-medium text-brand">{{ $displayName }}</span>
                        <span class="text-brand-muted">{{ $displayEmail }}</span>
                    </div>

                    <x-register.stepper
                        :current="$currentStep"
                        :steps="['Activités', 'Liens']"
                    />

                    <form wire:submit="save" class="flex flex-col gap-8">

                        {{-- ===================== ÉTAPE 1 : PROFIL ===================== --}}
                        <div @class(['flex flex-col gap-6' => $currentStep === 1, 'hidden' => $currentStep !== 1])>
                            <h2 class="font-serif text-2xl font-bold text-brand">Mon profil artiste</h2>

                            {{-- Portrait --}}
                            <div class="flex flex-col gap-1">
                                <label class="block text-sm font-medium text-brand">Photo de profil</label>
                                <x-ds.photo-upload
                                    :current-image-url="$currentImageUrl"
                                    :error="$errors->first('photo')"
                                />
                            </div>

                            <x-ds.textarea
                                wire:model.blur="biography"
                                name="biography"
                                label="Description de mon activité *"
                                :rows="6"
                                description="Présentez votre parcours, votre démarche et vos projets."
                                :error="$errors->first('biography')"
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

                            <x-ds.input
                                label="Lieu de résidence"
                                :value="$displayLocality"
                                disabled
                                description="Renseigné lors de votre demande de référencement."
                            />

                            {{-- Activités principales (max 4) --}}
                            <div class="flex flex-col gap-3">
                                <label class="block text-sm font-medium text-brand">
                                    Activités principales <span class="font-normal text-brand-muted">(max. 4)</span>
                                </label>
                                @if (count($activities) > 0)
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($activities as $i => $activity)
                                            <span class="inline-flex items-center gap-1.5 border border-brand-muted bg-brand-paper px-3 py-1 text-sm text-brand"
                                                  wire:key="act-{{ $i }}">
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
                                        <x-ds.btn type="button" variant="secondary" size="sm" wire:click="addActivity">
                                            Ajouter
                                        </x-ds.btn>
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
                                                  wire:key="sec-act-{{ $i }}">
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
                                    <x-ds.btn type="button" variant="secondary" size="sm" wire:click="addSecondaryActivity">
                                        Ajouter
                                    </x-ds.btn>
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
                                                  wire:key="kw-{{ $i }}">
                                                {{ $keyword }}
                                                <button type="button" wire:click="removeKeyword({{ $i }})" class="text-brand-muted hover:text-brand" aria-label="Retirer">
                                                    <x-picto name="close" class="size-3.5" />
                                                </button>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="flex gap-2">
                                    <x-ds.input
                                        wire:model="newKeyword"
                                        name="newKeyword"
                                        placeholder="Ex. : paysage, abstraction…"
                                        wire:keydown.enter.prevent="addKeyword"
                                        class="flex-1"
                                    />
                                    <x-ds.btn type="button" variant="secondary" size="sm" wire:click="addKeyword">
                                        Ajouter
                                    </x-ds.btn>
                                </div>
                            </div>
                        </div>

                        {{-- ===================== ÉTAPE 2 : LIENS ===================== --}}
                        <div @class(['flex flex-col gap-6' => $currentStep === 2, 'hidden' => $currentStep !== 2])>
                            <h2 class="font-serif text-2xl font-bold text-brand">Mes liens</h2>

                            {{-- Personal links --}}
                            <div class="flex flex-col gap-3">
                                <div class="flex items-center justify-between">
                                    <label class="block text-sm font-medium text-brand">
                                        Espaces personnels <span class="font-normal text-brand-muted">(max. 6)</span>
                                    </label>
                                    @if (count($links) < 6)
                                        <x-ds.btn type="button" variant="secondary" size="sm" icon="plus" wire:click="addLink">
                                            Ajouter
                                        </x-ds.btn>
                                    @endif
                                </div>
                                <div class="flex flex-col gap-3">
                                    @forelse ($links as $index => $link)
                                        <div class="flex flex-col gap-3 border border-brand-hairline p-4 sm:flex-row sm:items-end"
                                             wire:key="setup-link-{{ $index }}">
                                            <div class="w-full sm:w-32">
                                                <x-ds.input wire:model="links.{{ $index }}.label" label="Libellé" />
                                            </div>
                                            <div class="flex-1">
                                                <x-ds.input wire:model="links.{{ $index }}.url" label="URL" type="url" />
                                            </div>
                                            <x-ds.btn type="button" variant="secondary" size="sm" icon="close" wire:click="removeLink({{ $index }})">
                                                Retirer
                                            </x-ds.btn>
                                        </div>
                                    @empty
                                        <p class="text-sm text-brand-muted">Aucun lien ajouté.</p>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Collaborations --}}
                            <div class="flex flex-col gap-3">
                                <div class="flex items-center justify-between">
                                    <label class="block text-sm font-medium text-brand">Collaborations</label>
                                    <x-ds.btn type="button" variant="secondary" size="sm" icon="plus" wire:click="addCollaboration">
                                        Ajouter
                                    </x-ds.btn>
                                </div>
                                <div class="flex flex-col gap-3">
                                    @forelse ($collaborations as $index => $collab)
                                        <div class="flex flex-col gap-3 border border-brand-hairline p-4 sm:flex-row sm:items-end"
                                             wire:key="setup-collab-{{ $index }}">
                                            <div class="flex-1">
                                                <x-ds.input wire:model="collaborations.{{ $index }}.name" label="Nom" />
                                            </div>
                                            <div class="flex-1">
                                                <x-ds.input wire:model="collaborations.{{ $index }}.url" label="URL (facultatif)" type="url" />
                                            </div>
                                            <x-ds.btn type="button" variant="secondary" size="sm" icon="close" wire:click="removeCollaboration({{ $index }})">
                                                Retirer
                                            </x-ds.btn>
                                        </div>
                                    @empty
                                        <p class="text-sm text-brand-muted">Aucune collaboration ajoutée.</p>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Display contact button --}}
                            <div class="flex items-start gap-3 border border-brand-hairline p-4">
                                <input
                                    type="checkbox"
                                    id="display_contact_button"
                                    wire:model="display_contact_button"
                                    class="mt-0.5 size-4 accent-brand-teal"
                                />
                                <label for="display_contact_button" class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-brand">Afficher le bouton « Me contacter »</span>
                                    <span class="text-brand-muted">Votre adresse e-mail sera visible sous forme de lien de contact sur votre profil public.</span>
                                </label>
                            </div>
                        </div>

                        {{-- Navigation --}}
                        <div class="flex flex-col items-stretch gap-3 border-t border-brand-hairline pt-6 sm:flex-row sm:items-center sm:justify-between">
                            @if ($currentStep > 1)
                                <x-ds.btn type="button" variant="secondary" size="md" icon="arrow-left" wire:click="previousStep">
                                    Précédent
                                </x-ds.btn>
                            @else
                                <span class="hidden sm:block"></span>
                            @endif

                            @if ($currentStep < $totalSteps)
                                <x-ds.btn type="button" variant="primary" size="md" icon-trailing="arrow-right" wire:click="nextStep">
                                    Étape suivante
                                </x-ds.btn>
                            @else
                                <x-ds.btn type="submit" variant="primary" size="md" icon-trailing="check">
                                    Enregistrer mon profil
                                </x-ds.btn>
                            @endif
                        </div>

                    </form>
                </div>
            @endif
        </div>
    </x-ds.section>
</div>

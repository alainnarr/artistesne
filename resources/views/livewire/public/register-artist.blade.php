@php
    $otherActivity = config('taxonomy.other_value');
@endphp

<div>
    {{-- En-tête --}}
    <x-ds.section variant="paper" padding="none" class="pt-16">
        <x-ds.hero
            variant="page"
            title="Demande de"
            accent="référencement"
        >
            <x-slot:lead-slot>
                <p>Pour figurer sur l'annuaire, merci de compléter ce formulaire de demande de référencement. Les informations transmises permettront au SCNE de valider votre demande. En cas de validation, vous serez invité·e à créer votre profil public sur la base de ces informations.</p>
            </x-slot:lead-slot>
        </x-ds.hero>
    </x-ds.section>

    {{-- Bloc formulaire --}}
    <x-ds.section variant="cream">
        {{-- Retour vers espace artistes --}}
        <div class="mx-auto max-w-[928px] -mt-2 mb-6 hidden sm:block">
            <a href="{{ route('artist.login') }}" wire:navigate
               class="inline-flex items-center gap-2 text-sm font-medium text-brand-muted transition hover:text-brand">
                <x-picto name="arrow-left" class="size-4" />
                Retour à l'espace artistes
            </a>
        </div>
        <div class="mx-auto max-w-[928px] bg-brand-paper px-6 py-12 shadow-[0_4px_16px_rgba(27,62,61,0.04)] sm:px-16 sm:py-16">
                @if ($submitted)
                    <div class="mx-auto flex max-w-lg flex-col items-center gap-4 text-center">
                        <span class="flex size-12 items-center justify-center rounded-lg bg-brand-mint text-brand-teal">
                            <x-picto name="check" class="size-6" />
                        </span>
                        <x-ds.section-heading>Votre demande a bien été transmise</x-ds.section-heading>
                        <p class="text-base text-brand-muted">
                            Votre demande de référencement a bien été reçue. Le SCNE l'examinera dans les meilleurs délais.
                            Vous recevrez ensuite un e-mail vous informant de la suite donnée. Pensez à vérifier vos spams.
                        </p>
                    </div>
                @else
                    <div class="register-form mx-auto flex w-full max-w-[500px] flex-col gap-8">
                        <x-ds.stepper :current="$currentStep" :steps="['Identité', 'Activités', 'Documents']" />

                        <form wire:submit="submit" class="flex flex-col gap-8">

                            {{-- ===================== ÉTAPE 1 : IDENTITÉ ===================== --}}
                            <div @class(['flex flex-col gap-8' => $currentStep === 1, 'hidden' => $currentStep !== 1])>
                                <section class="flex flex-col gap-5">
                                    <x-ds.section-heading>Identité</x-ds.section-heading>

                                    <x-ds.field
                                        wire:model.blur.live="full_name"
                                        label="Nom complet"
                                        required
                                        description="Information affichée publiquement si le Nom d'artiste n'est pas complété."
                                    />

                                    <div class="flex flex-col gap-3">
                                        <x-ds.field wire:model.blur.live="artist_name" label="Nom d'artiste" />
                                        <x-ds.checkbox wire:model="show_artist_name" name="show_artist_name" label="Afficher mon nom d'artiste sur la page. Attention : ce choix est définitif et déterminera l'adresse de votre page de profil (artistes.ne.ch/nomcomplet ou artistes.ne.ch/nom-artiste)" />
                                    </div>

                                    <x-ds.datepicker
                                        wireModel="birth_date"
                                        label="Date de naissance *"
                                        :max="now()->format('Y-m-d')"
                                        :error="$errors->first('birth_date')"
                                        description="Format JJ.MM.AAAA - Non public"
                                    />

                                    <div class="flex flex-col gap-3">
                                        <x-ds.field
                                            wire:model.blur.live="email"
                                            type="email"
                                            label="Email"
                                            required
                                        />
                                        <x-ds.checkbox wire:model="display_contact_button" name="display_contact_button" label="Je souhaite qu'un bouton contact soit affiché sur ma fiche avec cet email (mail to)" />
                                    </div>

                                    <div class="flex flex-col gap-1">
                                        <label for="field-phone" class="text-[12px] leading-[17px] font-medium text-brand">Téléphone <span aria-hidden="true">*</span></label>
                                        {{-- wire:ignore prevents Livewire morphing from destroying the intl-tel-input widget --}}
                                        <div wire:ignore data-intl-phone-field
                                            data-phone-initial-country="CH"
                                            data-phone-has-error="{{ $errors->has('phone') ? '1' : '0' }}"
                                            class="iti-wrapper"
                                        >
                                            <input
                                                id="field-phone"
                                                type="tel"
                                                placeholder="79 123 45 67"
                                                class="h-14 w-full rounded-[2px] border bg-brand-paper px-4 text-base text-brand placeholder-brand-muted/70 focus:outline-none @error('phone') border-red-500 @else border-brand-muted @enderror"
                                            >
                                        </div>
                                        @error('phone')
                                            <p class="text-[12px] leading-[17px] font-medium text-red-600">{{ $message }}</p>
                                        @else
                                            <p class="text-[12px] leading-[17px] font-medium text-brand-muted">Uniquement pour les échanges avec le SCNE - Non public.</p>
                                        @enderror
                                    </div>
                                </section>

                                <section class="flex flex-col gap-5">
                                    <x-ds.section-heading>Territorialité</x-ds.section-heading>

                                    <x-locality-combobox
                                        wire:model.live="locality"
                                        label="Lieu de résidence"
                                        :groups="$this->localityGroups"
                                        placeholder="Sélectionner…"
                                        required
                                    />

                                    @if ($this->isOutsideCanton)
                                        <div class="flex flex-col gap-5" x-transition>
                                            <x-ds.field wire:model.blur.live="commune" label="Commune de résidence" required />
                                            <div class="flex flex-col gap-1.5">
                                                <p class="text-sm text-brand-muted">
                                                    Si vous résidez hors du canton de Neuchâtel, décrivez votre ancrage dans le tissu
                                                    culturel neuchâtelois (activité régulière et significative, partenariats, collaborations).
                                                </p>
                                                <x-ds.field
                                                    as="textarea"
                                                    wire:model.blur.live="canton_link"
                                                    label="Description de mon activité"
                                                    :rows="4"
                                                    :maxlength="500"
                                                    required
                                                    description="500 caractères maximum."
                                                />
                                            </div>
                                        </div>
                                    @endif
                                </section>
                            </div>

                            {{-- ===================== ÉTAPE 2 : ACTIVITÉS ===================== --}}
                            <div @class(['flex flex-col gap-8' => $currentStep === 2, 'hidden' => $currentStep !== 2])>
                                <section class="flex flex-col gap-5">
                                    <x-ds.section-heading>Domaines et activités</x-ds.section-heading>

                                    <x-ds.field as="select" wire:model.live="main_domain" label="Domaine principal" required>
                                        @foreach ($this->domainOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </x-ds.field>

                                    <x-ds.field
                                        as="select"
                                        wire:model.live="main_activity"
                                        label="Activité principale"
                                        required
                                        :disabled="blank($main_domain)"
                                    >
                                        @foreach ($this->availableActivities as $id => $label)
                                            <option value="{{ $id }}">
                                                {{ $id === $otherActivity ? config('taxonomy.other_label') : $label }}
                                            </option>
                                        @endforeach
                                    </x-ds.field>

                                    @if ($this->isOtherActivity)
                                        <div class="flex flex-col gap-1.5" x-transition>
                                            <p class="text-base text-brand">Merci de préciser votre activité</p>
                                            <x-ds.field
                                                wire:model.blur.live="main_activity_other"
                                                label="Autre activité principale"
                                                required
                                            />
                                        </div>
                                    @endif
                                </section>

                                <section class="flex flex-col gap-5">
                                    <div class="flex flex-col gap-2">
                                        <x-ds.section-heading>Professionnalisme</x-ds.section-heading>
                                        <p class="text-base text-brand">
                                            Au moins 2 des 3 critères suivants doivent être remplis. Aucun critère n'est obligatoire
                                            individuellement, leur combinaison permet d'apprécier le niveau de professionnalisme de la pratique.
                                        </p>
                                    </div>

                                    <x-ds.field wire:model.blur.live="training" label="Formation artistique" />
                                    <x-ds.field
                                        as="textarea"
                                        wire:model.blur.live="paid_activity"
                                        label="Activité"
                                        :rows="2"
                                        description="Préciser le contexte d'activité régulière et rémunérée dans le domaine concerné."
                                    />
                                    <x-ds.field
                                        as="textarea"
                                        wire:model.blur.live="recognition"
                                        label="Reconnaissance"
                                        :rows="2"
                                        description="Précisez : prix, résidences, collaborations institutionnelles … dans le domaine concerné."
                                    />

                                    @if ($this->criteriaCount < 2)
                                        <div class="flex flex-col gap-1.5" x-transition>
                                            <p class="text-base text-brand">
                                                Si un seul critère sur 3 est rempli, merci d'indiquer au moins une réalisation artistique
                                                dans un cadre professionnel au cours des 3 dernières années.
                                            </p>
                                            <x-ds.field
                                                as="textarea"
                                                wire:model.blur.live="recent_achievement"
                                                label="Réalisation artistique en contexte professionnel"
                                                :rows="3"
                                                required
                                                description="Exemple : exposition, concert programmé, publication éditée, collaboration professionnelle attestée."
                                            />
                                        </div>
                                    @else
                                        <x-ds.field
                                            as="textarea"
                                            wire:model.blur.live="recent_achievement"
                                            label="Réalisation artistique en contexte professionnel"
                                            :rows="3"
                                            description="Exemple : exposition, concert programmé, publication éditée, collaboration professionnelle attestée."
                                        />
                                    @endif
                                </section>

                                <section class="flex flex-col gap-5">
                                    <div class="flex flex-col gap-2">
                                        <x-ds.section-heading>Temporalité</x-ds.section-heading>
                                        <p class="text-base text-brand">
                                            Décrivez brièvement votre dernière activité significative en précisant le contexte
                                            (année, lieu, type d'événement ou de projet).
                                        </p>
                                    </div>
                                    <x-ds.field
                                        as="textarea"
                                        wire:model.blur.live="last_activity"
                                        label="Dernière activité significative"
                                        :rows="3"
                                        :maxlength="500"
                                        description="500 caractères maximum."
                                    />
                                </section>
                            </div>

                            {{-- ===================== ÉTAPE 3 : DOCUMENTS ===================== --}}
                            <div @class(['flex flex-col gap-8' => $currentStep === 3, 'hidden' => $currentStep !== 3])>
                                <section class="flex flex-col gap-4">
                                    <div class="flex flex-col gap-1">
                                        <x-ds.section-heading>Documents complémentaires</x-ds.section-heading>
                                        <p class="text-base text-brand">Déposer tout élément permettant d'appuyer votre demande : CV, dossier artistique, lien de presse.</p>
                                    </div>

                                    <div class="flex flex-col gap-1.5">
                                        <p class="font-serif text-lg font-bold text-brand">Documents</p>
                                        <div
                                            x-data="{
                                                dragging: false,
                                                clientErrors: [],
                                                maxSize: 5 * 1024 * 1024,
                                                allowedExtensions: ['pdf', 'jpg', 'jpeg', 'png'],
                                                validate(files) {
                                                    const errors = [];
                                                    Array.from(files).forEach(f => {
                                                        const ext = f.name.split('.').pop().toLowerCase();
                                                        if (!this.allowedExtensions.includes(ext)) {
                                                            errors.push(`« ${f.name} » : format non autorisé (formats acceptés : .pdf, .jpg, .png).`);
                                                        } else if (f.size > this.maxSize) {
                                                            errors.push(`« ${f.name} » : fichier trop volumineux (5 Mo maximum).`);
                                                        }
                                                    });
                                                    this.clientErrors = errors;
                                                },
                                                handleDrop(e) {
                                                    this.dragging = false;
                                                    const el = document.getElementById('registration-documents');
                                                    if (!el || !e.dataTransfer?.files?.length) return;
                                                    this.validate(e.dataTransfer.files);
                                                    // Assign via DataTransfer to trigger Livewire's file watcher
                                                    const dt = new DataTransfer();
                                                    Array.from(el.files ?? []).forEach(f => dt.items.add(f));
                                                    Array.from(e.dataTransfer.files).forEach(f => dt.items.add(f));
                                                    el.files = dt.files;
                                                    el.dispatchEvent(new Event('change', { bubbles: true }));
                                                },
                                            }"
                                            class="flex flex-col gap-1.5"
                                        >
                                            <label
                                                for="registration-documents"
                                                x-on:dragover.prevent="dragging = true"
                                                x-on:dragleave.prevent="dragging = false"
                                                x-on:drop.prevent="handleDrop($event)"
                                                :class="dragging ? 'border-brand-teal bg-brand-mint/10' : 'border-zinc-300 hover:border-brand hover:bg-brand-mint/10'"
                                                class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-[2px] border bg-brand-paper px-4 py-8 text-center text-sm text-brand-muted transition"
                                            >
                                                <x-picto name="file-upload" class="size-8 text-brand" />
                                                <span>Glissez vos documents ici</span>
                                                <span class="text-xs">ou</span>
                                                <span class="font-medium text-brand underline">Parcourir vos fichiers</span>
                                                <input
                                                    id="registration-documents"
                                                    type="file"
                                                    wire:model="documents"
                                                    multiple
                                                    accept=".pdf,.jpg,.jpeg,.png"
                                                    class="hidden"
                                                    x-on:change="validate($event.target.files)"
                                                >
                                            </label>
                                            <p class="text-sm text-brand-muted">Limité à 5 Mo par fichier. Formats acceptés : .pdf, .jpg, .png.</p>

                                            <template x-if="clientErrors.length > 0">
                                                <ul class="flex flex-col gap-1">
                                                    <template x-for="(error, i) in clientErrors" :key="i">
                                                        <li class="text-sm text-red-600" x-text="error"></li>
                                                    </template>
                                                </ul>
                                            </template>
                                        </div>

                                        <div wire:loading wire:target="documents">
                                            <p class="text-sm text-brand-muted">Téléversement en cours…</p>
                                        </div>


                                        @if (filled($documents))
                                            <ul class="flex flex-col gap-1">
                                                @foreach ($documents as $index => $document)
                                                    <li class="flex items-center gap-2 text-sm text-brand" wire:key="doc-{{ $index }}">
                                                        <x-picto name="file-upload" class="size-4 shrink-0 text-brand-muted" />
                                                        <span class="min-w-0 flex-1 truncate">{{ $document->getClientOriginalName() }}</span>
                                                        <button
                                                            type="button"
                                                            wire:click="removeDocument({{ $index }})"
                                                            class="shrink-0 text-brand-muted transition hover:text-red-500"
                                                            aria-label="Supprimer {{ $document->getClientOriginalName() }}"
                                                        >
                                                            <x-picto name="close" class="size-4" />
                                                        </button>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif

                                        @error('documents.*')
                                            <p class="text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </section>

                                <section class="flex flex-col gap-3">
                                    <p class="font-serif text-lg font-bold text-brand">Liens</p>
                                    @foreach ($links as $index => $link)
                                        <div class="flex items-center gap-2" wire:key="link-{{ $index }}">
                                            <div class="relative flex-1" x-data="{ filled: @js(filled($link)), focused: false }">
                                                <input
                                                    id="field-link-{{ $index }}"
                                                    type="url"
                                                    placeholder=" "
                                                    wire:model.blur.live="links.{{ $index }}"
                                                    x-on:focus="focused = true"
                                                    x-on:blur="focused = false"
                                                    x-on:input="filled = $event.target.value !== ''"
                                                    class="peer h-14 w-full rounded-[2px] border border-zinc-300 bg-brand-paper px-4 text-base text-brand placeholder-transparent focus:border-brand focus:outline-none"
                                                >
                                                <label
                                                    for="field-link-{{ $index }}"
                                                    x-bind:class="(filled || focused) ? 'top-0 -translate-y-1/2 text-xs text-brand' : 'top-1/2 -translate-y-1/2 text-base text-brand-muted'"
                                                    class="pointer-events-none absolute start-3 bg-brand-paper px-1 leading-none transition-all duration-150"
                                                >
                                                    Autre lien
                                                </label>
                                            </div>
                                            @if (count($links) > 1)
                                                <button type="button" wire:click="removeLink({{ $index }})" aria-label="Supprimer le lien" class="inline-flex size-10 items-center justify-center text-brand-muted transition hover:text-red-600">
                                                    <x-picto name="close" class="size-4" />
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                    <div>
                                        <button type="button" wire:click="addLink" class="inline-flex items-center gap-2 text-base font-medium text-brand transition hover:opacity-70">
                                            <x-picto name="plus" class="size-4" />
                                            <span class="underline underline-offset-2">Ajouter un lien supplémentaire</span>
                                        </button>
                                    </div>
                                </section>

                                <section class="flex flex-col gap-3">
                                    <p class="font-serif text-lg font-bold text-brand">Attestation</p>
                                    <p class="text-base text-brand">Le SCNE peut solliciter des compléments avant de statuer sur votre demande de référencement.</p>
                                    <x-ds.checkbox
                                        wire:model="attests"
                                        name="attests"
                                        label="J'atteste de l'exactitude des informations transmises."
                                        required
                                    />
                                    @error('attests')
                                        <p class="text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </section>

                                @if (config('services.turnstile.enabled'))
                                    <section class="flex flex-col gap-2">
                                        <p class="font-serif text-lg font-bold text-brand">Captcha</p>
                                        <div
                                            wire:ignore
                                            x-data
                                            x-init="
                                                turnstile.render($el, {
                                                    sitekey: @js(config('services.turnstile.site_key')),
                                                    callback: (token) => $wire.set('turnstileToken', token),
                                                })
                                            "
                                        ></div>
                                        @error('turnstileToken')
                                            <p class="text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </section>
                                @endif
                            </div>

                            {{-- ===================== NAVIGATION ===================== --}}
                            <div @class([
                                'flex gap-8 pt-6',
                                'justify-center' => $currentStep === 1,
                                'justify-between' => $currentStep > 1,
                            ])>
                                @if ($currentStep > 1)
                                    <x-ds.btn type="button" variant="secondary" size="md" icon="arrow-left" wire:click="previousStep">
                                        Étape précédente
                                    </x-ds.btn>
                                @endif

                                @if ($currentStep < $totalSteps)
                                    <x-ds.btn type="button" variant="primary" size="md" icon-trailing="arrow-right" wire:click="nextStep">
                                        Étape suivante
                                    </x-ds.btn>
                                @else
                                    <x-ds.btn type="submit" variant="primary" size="md" icon-trailing="check" wire:loading.attr="disabled" wire:target="submit">
                                        Valider la demande
                                    </x-ds.btn>
                                @endif
                            </div>
                        </form>
                    </div>
                @endif
        </div>
    </x-ds.section>
</div>

@if (config('services.turnstile.enabled'))
    @push('scripts')
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endpush
@endif

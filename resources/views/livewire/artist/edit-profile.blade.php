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

            <form wire:submit="save" class="register-form flex flex-col gap-8">
                <x-ds.stepper
                    :current="$currentStep"
                    :steps="['Activités', 'Liens']"
                />

                {{-- ===================== ÉTAPE 1 : ACTIVITÉS ===================== --}}
                <div @class(['flex flex-col gap-10' => $currentStep === 1, 'hidden' => $currentStep !== 1])>
                    {{-- Mes données personnelles --}}
                    <section class="flex flex-col gap-5">
                        <h2 class="font-serif text-2xl font-bold text-brand">Mes données personnelles</h2>
                        <x-ds.field
                            label="Nom complet"
                            :value="$fullName"
                            disabled
                            description="Issu de votre demande de référencement — non modifiable ici."
                        />
                        <x-ds.field
                            label="Email"
                            type="email"
                            :value="$email"
                            disabled
                            description="Issu de votre demande de référencement — non modifiable ici."
                        />
                        <x-ds.field
                            wire:model="artist_name"
                            label="Nom d'artiste"
                        />
                        <x-ds.field
                            wire:model="city"
                            label="Lieu de résidence"
                        />
                    </section>

                    {{-- Mon profil artiste --}}
                    <x-artist.profile-domains
                        :discipline-options="$disciplineOptions"
                        :main-activity-options="$mainActivityOptions"
                        :secondary-activity-options="$secondaryActivityOptions"
                        :current-image-url="$currentImageUrl"
                        :discipline_main_id="$discipline_main_id"
                        :activities="$activities"
                        :secondary_activities="$secondary_activities"
                        :keywords="$keywords"
                    />
                </div>

                {{-- ===================== ÉTAPE 2 : LIENS ===================== --}}
                <div @class(['flex flex-col gap-10' => $currentStep === 2, 'hidden' => $currentStep !== 2])>
                    <x-artist.profile-links :links="$links" :collaborations="$collaborations" />
                </div>

                {{-- Navigation --}}
                <div class="flex flex-col items-stretch gap-3 border-t border-brand-hairline pt-6 sm:flex-row sm:items-center sm:justify-between">
                    @if ($currentStep > 1)
                        <x-ds.btn type="button" variant="secondary" size="md" icon="arrow-left" wire:click="previousStep">
                            Précédent
                        </x-ds.btn>
                    @else
                        <x-ds.btn variant="secondary" size="md" :href="route('artist.login')" wire:navigate>
                            Annuler
                        </x-ds.btn>
                    @endif

                    @if ($currentStep < $totalSteps)
                        <x-ds.btn type="button" variant="primary" size="md" icon-trailing="arrow-right" wire:click="nextStep">
                            Étape suivante
                        </x-ds.btn>
                    @else
                        <x-ds.btn type="submit" variant="primary" size="md" icon-trailing="arrow-right" :disabled="$hasPendingChange">
                            Soumettre la modification
                        </x-ds.btn>
                    @endif
                </div>
            </form>
        </div>
    </x-ds.section>
</div>

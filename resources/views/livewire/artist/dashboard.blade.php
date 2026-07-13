<div>
    {{-- Hero "Espace Artistes" (Figma 561:50886) --}}
    <x-ds.section variant="paper">
        <x-ds.hero
            variant="page"
            title="Espace"
            accent="Artistes"
            lead="Votre espace personnel pour gérer votre présence sur Artistes.ne. Référencez-vous, accédez à votre profil et gérez votre compte."
        />
    </x-ds.section>

    {{-- Flash message (reactivation success etc.) --}}
    @if (session('status'))
        <x-ds.section variant="paper" padding="sm">
            <div class="rounded border border-green-300 bg-green-50 px-4 py-3 text-green-800 text-sm">
                {{ session('status') }}
            </div>
        </x-ds.section>
    @endif

    {{-- Reactivation banner when artist profile is Draft / disabled --}}
    @if ($artist && ! $artist->isPublished())
        <x-ds.section variant="paper" padding="sm">
            <div class="flex flex-col gap-4 rounded border border-brand-muted/40 bg-brand-cream px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-semibold text-brand">Votre profil est actuellement inactif</p>
                    <p class="mt-1 text-sm text-brand-muted">
                        Votre fiche n'est plus visible dans l'annuaire. Vous pouvez la réactiver à tout moment.
                    </p>
                </div>
                <div class="shrink-0">
                    <x-ds.btn
                        variant="primary"
                        size="md"
                        wire:click="reactivate"
                        wire:loading.attr="disabled"
                    >
                        Réactiver mon profil
                    </x-ds.btn>
                </div>
            </div>
        </x-ds.section>
    @endif

    {{-- Carte centrale sur fond cream (Figma 561:50890) --}}
    <x-ds.section variant="cream">
        <div class="mx-auto w-full max-w-[928px] bg-brand-paper px-6 py-12 sm:px-16 sm:py-16">
            <div class="flex flex-col gap-12">

                {{-- ============= RÉFÉRENCEMENT ============= --}}
                <section class="flex flex-col gap-6">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">Référencement</h2>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        {{-- Carte « Se faire référencer » --}}
                        @if (! $artist)
                            <article class="flex flex-col gap-5 bg-brand p-6 text-brand-paper">
                                <h3 class="font-serif text-xl font-bold leading-snug">
                                    Se faire référencer sur la plateforme
                                </h3>
                                <p class="text-sm leading-relaxed text-brand-paper/85">
                                    Vous n'êtes pas encore référencé·e sur l'annuaire ? Soumettez votre demande
                                    en complétant le formulaire de référencement.
                                </p>
                                <div class="mt-auto pt-2">
                                    <x-ds.btn
                                        variant="primary"
                                        size="md"
                                        :href="route('artist.register')"
                                        wire:navigate
                                    >
                                        Créer un profil artiste
                                    </x-ds.btn>
                                </div>
                            </article>
                        @else
                            {{-- Si déjà artiste : carte « Mon profil » --}}
                            <article class="flex flex-col gap-5 bg-brand p-6 text-brand-paper">
                                <h3 class="font-serif text-xl font-bold leading-snug">
                                    Mon profil public
                                </h3>
                                <p class="text-sm leading-relaxed text-brand-paper/85">
                                    Consultez votre fiche publique et vérifiez les informations affichées
                                    sur l'annuaire.
                                </p>
                                <div class="mt-auto pt-2">
                                    @if ($artist->isPublished())
                                        <x-ds.btn
                                            variant="primary"
                                            size="md"
                                            :href="route('public.artist.show', $artist)"
                                        >
                                            Voir ma page publique
                                        </x-ds.btn>
                                    @else
                                        <x-ds.tag variant="secondary">
                                            {{ $artist->status->label() }}
                                        </x-ds.tag>
                                    @endif
                                </div>
                            </article>
                        @endif

                        {{-- Carte « Recevoir un lien » --}}
                        <article class="flex flex-col gap-5 bg-brand p-6 text-brand-paper">
                            <h3 class="font-serif text-xl font-bold leading-snug">
                                Recevoir un lien
                            </h3>
                            <p class="text-sm leading-relaxed text-brand-paper/85">
                                Vous avez fait votre demande de référencement il y a plus de 7 jours et vous
                                n'avez pas reçu de lien pour finaliser la création de votre profil.
                            </p>
                            <div class="mt-auto pt-2">
                                <x-ds.btn
                                    variant="primary"
                                    size="md"
                                    :href="route('login')"
                                >
                                    Redemander un lien
                                </x-ds.btn>
                            </div>
                        </article>
                    </div>
                </section>

                {{-- ============= MODIFIER / SUPPRIMER ============= --}}
                <section class="flex flex-col gap-4">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">
                        Modifier ou supprimer votre compte
                    </h2>
                    <p class="text-base leading-relaxed text-brand">
                        Vous souhaitez modifier ou supprimer votre compte ?
                    </p>
                    <p class="text-base leading-relaxed text-brand">
                        Transmettez votre demande via le lien :
                        @if ($artist)
                            <a
                                href="{{ route('artist.profile.edit') }}"
                                wire:navigate
                                class="ml-1 font-medium underline underline-offset-2 hover:no-underline"
                            >
                                Demande de modifications
                            </a>
                        @else
                            <a
                                href="{{ route('contact') }}"
                                wire:navigate
                                class="ml-1 font-medium underline underline-offset-2 hover:no-underline"
                            >
                                Demande de modifications
                            </a>
                        @endif
                    </p>
                </section>

                {{-- ============= RÉACTIVER ============= --}}
                <section class="flex flex-col gap-4">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">
                        Réactiver mon compte
                    </h2>
                    <p class="text-base leading-relaxed text-brand">
                        Tous les six mois, nous vous invitons à confirmer l'actualité de votre profil.
                        Sans réponse de votre part, celui-ci est automatiquement désactivé.
                    </p>
                    <p class="text-base leading-relaxed text-brand">
                        Vous pouvez le réactiver à tout moment :
                        <a
                            href="{{ route('contact') }}"
                            wire:navigate
                            class="ml-1 font-medium underline underline-offset-2 hover:no-underline"
                        >
                            Demande de réactivation
                        </a>
                    </p>
                </section>

                {{-- Modification en cours (contextuel) --}}
                @if ($pendingChange)
                    <div class="border-l-4 border-brand-teal bg-brand-cream/60 p-5">
                        <h3 class="font-serif text-lg font-bold text-brand">Modification en cours de validation</h3>
                        <p class="mt-2 text-base text-brand-muted">
                            Votre dernière proposition est en attente de validation par l'administration.
                            Vous pourrez en soumettre une nouvelle dès qu'elle aura été traitée.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </x-ds.section>
</div>

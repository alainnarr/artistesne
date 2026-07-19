<div>
    {{-- Hero — Espace Artistes (Figma 561:50886) --}}
    <x-ds.section variant="paper" padding="none" class="pt-16 pb-12 sm:pb-16">
        <x-ds.hero
            variant="home"
            title="Espace"
            accent="Artistes"
        >
            <x-slot:lead-slot>
                <p>Votre espace personnel pour gérer votre présence sur Artistes.ne.<br>Référencez-vous, accédez à votre profil et gérez votre compte.</p>
            </x-slot:lead-slot>
        </x-ds.hero>
    </x-ds.section>

    {{-- Corps — Espace Artistes (Figma 561:50889) --}}
    <x-ds.section variant="cream">
        <div class="mx-auto w-full max-w-[928px] bg-brand-paper p-6 sm:p-12 lg:p-16">
            <div class="flex flex-col gap-12 sm:gap-14">

                @if (session('error'))
                    <div class="flex items-start gap-4 border-l-4 border-red-500 bg-red-50 p-5">
                        <div>
                            <h2 class="font-serif text-lg font-bold text-brand">Lien de connexion invalide</h2>
                            <p class="mt-1 text-base text-brand-muted">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                {{-- Section 1 — Référencement --}}
                <section class="flex flex-col gap-6 sm:gap-8">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">Référencement</h2>

                    {{-- Push cards --}}
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                        {{-- Card 1 — Se faire référencer --}}
                        <div class="flex flex-col gap-6 rounded-sm bg-brand p-8 sm:p-12">
                            <div class="flex flex-col gap-4">
                                <h3 class="font-serif text-xl font-bold leading-snug text-brand-cream sm:text-2xl">
                                    Se faire référencer sur la plateforme
                                </h3>
                                <p class="text-base leading-relaxed text-brand-cream/90 sm:text-lg">
                                    Vous n'êtes pas encore référencé·e sur l'annuaire&nbsp;? Soumettez votre demande en complétant le formulaire de référencement.
                                </p>
                            </div>
                            <div class="mt-auto pt-2">
                                <x-ds.btn
                                    variant="primary"
                                    size="md"
                                    :href="route('public.artist-registration')"
                                    wire:navigate
                                >
                                    Créer un profil artiste
                                </x-ds.btn>
                            </div>
                        </div>

                        {{-- Card 2 — Recevoir / Accéder via lien magique --}}
                        <div class="flex flex-col gap-6 rounded-sm bg-brand p-8 sm:p-12">
                            <div class="flex flex-col gap-4">
                                <h3 class="font-serif text-xl font-bold leading-snug text-brand-cream sm:text-2xl">
                                    Recevoir un lien
                                </h3>
                                <p class="text-base leading-relaxed text-brand-cream/90 sm:text-lg">
                                    Vous êtes déjà référencé·e&nbsp;? Renseignez votre e-mail pour recevoir un lien de connexion sécurisé.
                                </p>
                            </div>

                            @if ($sent)
                                <div class="flex flex-col gap-1 rounded-sm bg-brand-mint/20 p-4">
                                    <p class="font-medium text-brand-cream">Vérifiez votre boîte mail</p>
                                    <p class="text-sm text-brand-cream/80">
                                        Si un compte est associé à <strong>{{ $email }}</strong>, vous recevrez un lien valable 60 minutes. Vérifiez vos spams.
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    wire:click="$set('sent', false)"
                                    class="self-start text-sm font-medium text-brand-mint underline-offset-2 hover:underline"
                                >
                                    Renvoyer un lien
                                </button>
                            @else
                                <form wire:submit="send" class="flex flex-col gap-4">
                                    <x-ds.field
                                        wire:model="email"
                                        type="email"
                                        label="Adresse e-mail"
                                        autofocus
                                    />
                                    <div>
                                        <x-ds.btn type="submit" variant="primary" size="md">
                                            Recevoir mon lien
                                        </x-ds.btn>
                                    </div>
                                </form>
                            @endif
                        </div>

                    </div>
                </section>

                {{-- Section 2 — Modifier ou supprimer votre compte --}}
                <section class="flex flex-col gap-6 sm:gap-8">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">Modifier ou supprimer votre compte</h2>
                    <div class="flex flex-col gap-4 text-base leading-relaxed text-brand sm:text-lg">
                        <p>Vous souhaitez modifier ou supprimer votre compte&nbsp;?</p>
                        <p>
                            Transmettez votre demande via le lien&nbsp;:
                            <a href="{{ route('public.modification-request') }}" wire:navigate class="font-medium underline underline-offset-2 hover:text-brand-teal">Demande de modifications</a>
                        </p>
                    </div>
                </section>

                {{-- Section 3 — Réactiver mon compte --}}
                <section class="flex flex-col gap-6 sm:gap-8">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">Réactiver mon compte</h2>
                    <div class="flex flex-col gap-4 text-base leading-relaxed text-brand sm:text-lg">
                        <p>Tous les six mois, nous vous invitons à confirmer l'actualité de votre profil. Sans réponse de votre part, celui-ci est automatiquement désactivé.</p>
                        <p>
                            Vous pouvez le réactiver à tout moment&nbsp;:
                            <a href="{{ route('public.reactivation-request') }}" wire:navigate class="font-medium underline underline-offset-2 hover:text-brand-teal">Demande de réactivation</a>
                        </p>
                    </div>
                </section>

            </div>
        </div>
    </x-ds.section>
</div>

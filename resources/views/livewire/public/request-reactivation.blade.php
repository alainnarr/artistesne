<div>
    {{-- Hero --}}
    <x-ds.section variant="paper">
        <x-ds.hero
            variant="page"
            title="Demande de"
            accent="réactivation"
        >
            <x-slot:lead-slot>
                <p>Votre profil a été désactivé suite à la non-confirmation semestrielle ? Demandez sa réactivation.</p>
            </x-slot:lead-slot>
        </x-ds.hero>
    </x-ds.section>

    {{-- Form card --}}
    <x-ds.section variant="cream">
        <div class="mx-auto w-full max-w-[928px] bg-brand-paper px-6 py-12 shadow-[0_4px_16px_rgba(27,62,61,0.04)] sm:px-16 sm:py-16">
            @if ($submitted)
                <div class="mx-auto flex max-w-lg flex-col items-center gap-4 text-center">
                    <span class="flex size-12 items-center justify-center rounded-lg bg-brand-mint text-brand-teal">
                        <x-picto name="check" class="size-6" />
                    </span>
                    <h2 class="font-serif text-2xl font-bold text-brand">Votre demande a bien été envoyée</h2>
                    <p class="text-base text-brand-muted">
                        Le service de la culture vous répondra dans les meilleurs délais.
                    </p>
                </div>
            @else
                <form wire:submit="submit" class="mx-auto flex w-full max-w-[500px] flex-col gap-6">
                    <section class="flex flex-col gap-4">
                        <h2 class="font-serif text-2xl font-bold text-brand">Votre email</h2>
                        <p class="text-base leading-relaxed text-brand">Indiquer l'adresse email de votre profil.</p>
                        <x-ds.field
                            wire:model.blur="email"
                            type="email"
                            label="Email"
                            required
                        />
                    </section>

                    <div class="flex justify-end">
                        <x-ds.btn type="submit" variant="primary" size="md" icon-trailing="arrow-right">
                            Faire la demande
                        </x-ds.btn>
                    </div>
                </form>
            @endif
        </div>
    </x-ds.section>
</div>

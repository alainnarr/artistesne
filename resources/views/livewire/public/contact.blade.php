<div>
    {{-- Hero --}}
    <x-ds.section variant="paper">
        <x-ds.hero
            variant="page"
            title="Nous"
            accent="contacter"
            lead="Vous pouvez utiliser le formulaire ci-dessous pour prendre contact avec les administrateurs du site internet www.artistes.ne.ch."
        />
    </x-ds.section>

    {{-- Form card --}}
    <x-ds.section variant="cream">
        <div class="mx-auto w-full max-w-[928px] bg-brand-paper px-6 py-12 shadow-[0_4px_16px_rgba(27,62,61,0.04)] sm:px-16 sm:py-16">
            @if ($submitted)
                <div class="mx-auto flex max-w-lg flex-col items-center gap-4 text-center">
                    <span class="flex size-12 items-center justify-center rounded-lg bg-brand-mint text-brand-teal">
                        <x-picto name="check" class="size-6" />
                    </span>
                    <h2 class="font-serif text-2xl font-bold text-brand">Votre message a bien été envoyé</h2>
                    <p class="text-base text-brand-muted">
                        Le service de la culture vous répondra dans les meilleurs délais. Pensez à vérifier vos spams.
                    </p>
                </div>
            @else
                <form wire:submit="submit" class="ds-contact-form mx-auto flex w-full max-w-[500px] flex-col gap-6">
                    <h2 class="font-serif text-2xl font-bold text-brand">Formulaire de contact</h2>

                    <div class="flex flex-col gap-4">
                        <x-ds.field wire:model.blur="last_name" label="Nom" required />
                        <x-ds.field wire:model.blur="first_name" label="Prénom" required />
                        <x-ds.field wire:model.blur="email" type="email" label="Adresse email" required />
                        <x-ds.field wire:model.blur="subject" label="Objet" required />
                        <x-ds.field as="textarea" wire:model.blur="message" label="Message" :rows="5" required />
                    </div>

                    @if (config('services.turnstile.enabled'))
                        <div class="flex flex-col gap-2">
                            <p class="font-serif text-lg font-bold text-brand">Captcha</p>
                            <p class="text-xs font-light text-brand-muted">
                                Les données collectées sont utilisées par le service de la culture uniquement pour traiter votre demande.
                            <a href="{{ route('public.privacy') }}" wire:navigate class="underline underline-offset-2 hover:no-underline">En savoir plus sur la protection des données</a>.
                            </p>
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
                        </div>
                    @else
                        <p class="text-xs font-light text-brand-muted">
                            Les données collectées sont utilisées par le service de la culture uniquement pour traiter votre demande.
                        <a href="{{ route('public.privacy') }}" wire:navigate class="underline underline-offset-2 hover:no-underline">En savoir plus sur la protection des données</a>.
                        </p>
                    @endif

                    <div class="flex justify-center">
                        <x-ds.btn type="submit" variant="primary" size="md" wire:loading.attr="disabled" wire:target="submit">
                            Valider la demande
                        </x-ds.btn>
                    </div>
                </form>
            @endif
        </div>
    </x-ds.section>
</div>

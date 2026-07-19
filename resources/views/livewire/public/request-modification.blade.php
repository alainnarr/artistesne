<div>
    {{-- Hero --}}
    <x-ds.section variant="paper">
        <x-ds.hero
            variant="page"
            title="Demande de"
            accent="modification"
        >
            <x-slot:lead-slot>
                <p>Vous souhaitez mettre à jour votre profil ou faire une demande de suppression de compte</p>
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

                    {{-- Votre email --}}
                    <section class="flex flex-col gap-4">
                        <h2 class="font-serif text-2xl font-bold text-brand">Votre email</h2>
                        <p class="text-base leading-relaxed text-brand">Indiquer l'adresse email de votre profil</p>
                        <x-ds.field
                            wire:model.blur="email"
                            type="email"
                            label="Email"
                            required
                        />
                    </section>

                    {{-- Demande de modification --}}
                    <section class="flex flex-col gap-4">
                        <h2 class="font-serif text-2xl font-bold text-brand">Demande de modification</h2>
                        <p class="text-base leading-relaxed text-brand">Je souhaite</p>

                        <div class="flex flex-wrap gap-4">
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="request_type" value="update" class="sr-only" />
                                <span @class([
                                    'inline-flex items-center gap-2 rounded px-4 py-2.5 text-base border border-brand-mint bg-brand-paper transition',
                                    'text-brand shadow-[inset_0_0_0_1px_theme(colors.brand-mint)]' => $request_type === 'update',
                                    'text-brand-muted' => $request_type !== 'update',
                                ])>
                                    @if ($request_type === 'update')
                                        <x-picto name="check" class="size-4 text-brand-teal" />
                                    @endif
                                    Mettre à jour mon profil
                                </span>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="request_type" value="delete" class="sr-only" />
                                <span @class([
                                    'inline-flex items-center gap-2 rounded px-4 py-2.5 text-base border border-brand-mint bg-brand-paper transition',
                                    'text-brand shadow-[inset_0_0_0_1px_theme(colors.brand-mint)]' => $request_type === 'delete',
                                    'text-brand-muted' => $request_type !== 'delete',
                                ])>
                                    @if ($request_type === 'delete')
                                        <x-picto name="check" class="size-4 text-brand-teal" />
                                    @endif
                                    Supprimer mon compte
                                </span>
                            </label>
                        </div>
                    </section>

                    {{-- Captcha --}}
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

                    {{-- Submit --}}
                    <div class="flex justify-center pt-2">
                        <x-ds.btn type="submit" variant="primary" size="md" wire:loading.attr="disabled" wire:target="submit">
                            Faire la demande
                        </x-ds.btn>
                    </div>

                </form>
            @endif
        </div>
    </x-ds.section>
</div>

@if (config('services.turnstile.enabled'))
    @push('scripts')
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endpush
@endif

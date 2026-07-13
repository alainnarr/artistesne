{{--
    DS · Contact form — Figma node 561:51129 (section « Page du footer »).

    Formulaire de prise de contact (visuel uniquement) :
        - Titre de section
        - 4 champs courts (Nom, Prénom, Email, Sujet)
        - 1 zone de texte (Message)
        - Mention RGPD + captcha placeholder
        - CTA primaire d'envoi

    Props :
        $heading     — titre de la section formulaire
        $submitLabel — label du CTA d'envoi
        $wireSubmit  — directive Livewire `wire:submit` (peut être null pour rendu statique)
        $privacyHref — URL de la politique de confidentialité
--}}
@props([
    'heading'     => 'Formulaire de contact',
    'submitLabel' => 'Valider la demande',
    'wireSubmit'  => null,
    'privacyHref' => null,
])

<form
    @if ($wireSubmit) wire:submit="{{ $wireSubmit }}" @endif
    {{ $attributes->class('ds-contact-form flex w-full max-w-[500px] flex-col gap-6') }}
>
    <h2 class="font-serif text-2xl font-bold text-brand">{{ $heading }}</h2>

    <div class="flex flex-col gap-4">
        <x-ds.input name="last_name" label="Nom" required />
        <x-ds.input name="first_name" label="Prénom" required />
        <x-ds.input name="email" type="email" label="E-mail" required />
        <x-ds.input name="subject" label="Sujet" required />
        <x-ds.textarea name="message" label="Message" :rows="5" required />
    </div>

    <div class="flex flex-col gap-3">
        <h3 class="font-serif text-xl font-bold text-brand">Captcha</h3>
        <p class="text-xs font-light text-brand-muted">
            Les données collectées sont utilisées par le service de la culture uniquement pour traiter votre demande.
            @if ($privacyHref)
                <a href="{{ $privacyHref }}" class="underline underline-offset-2 hover:no-underline">En savoir plus sur la protection des données</a>.
            @endif
        </p>

        {{-- Captcha placeholder (visuel uniquement) --}}
        <div class="flex h-24 w-full max-w-sm items-center justify-center border border-brand-hairline bg-brand-track text-xs text-brand-muted">
            Captcha
        </div>
    </div>

    <div class="flex justify-center">
        <x-ds.btn type="submit" variant="primary" size="md">
            {{ $submitLabel }}
        </x-ds.btn>
    </div>
</form>

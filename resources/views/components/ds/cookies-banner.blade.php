{{--
    DS · Cookies banner — Figma node 2439:39114
    https://www.figma.com/design/quwzQh6G272IDJtnfF7teN/?node-id=2439-39114

    Bandeau d'acceptation des cookies — fond brand sombre, texte clair, CTA primaire.
    Persistance simple via localStorage (clé : artistes-ne-cookies).

    Props :
        $description — texte du bandeau
        $acceptLabel — label du CTA principal
        $linkLabel   — label du lien (politique de confidentialité)
        $linkHref    — URL du lien
--}}
@props([
    'description' => "Nous utilisons des cookies ainsi que différents outils d'analyse pour améliorer votre expérience sur notre site.",
    'acceptLabel' => 'Accepter',
    'linkLabel'   => 'Voir nos conditions générales de confidentialité',
    'linkHref'    => '#',
])

<div
    x-data="{ open: ! localStorage.getItem('artistes-ne-cookies') }"
    x-show="open"
    x-cloak
    class="ds-cookies-banner fixed inset-x-0 bottom-0 z-40 w-full bg-brand p-5 text-brand-paper shadow-2xl sm:p-6"
    role="dialog"
    aria-label="Bandeau cookies"
>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between sm:gap-6">
        <div class="flex-1 text-sm text-brand-paper/90">
            <p>{{ $description }}</p>
            @if ($linkHref)
                <a href="{{ $linkHref }}" class="mt-1 inline-block text-sm font-medium underline hover:no-underline">{{ $linkLabel }}</a>
            @endif
        </div>
        <div class="shrink-0">
            <x-ds.btn
                variant="primary"
                size="md"
                @click="localStorage.setItem('artistes-ne-cookies', '1'); open = false"
            >
                {{ $acceptLabel }}
            </x-ds.btn>
        </div>
    </div>
</div>

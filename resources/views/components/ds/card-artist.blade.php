{{--
    DS · Card Artist — Figma node 2439:38922
    https://www.figma.com/design/quwzQh6G272IDJtnfF7teN/?node-id=2439-38922

    Anatomie : container · titre · lieu · domaines (primaire+secondaire) ·
               divider · liste des activités · CTA "Voir profil" · drop shadow

    Props :
        $name              — Nom de l'artiste (string)
        $location          — Lieu de résidence
        $primaryDomain     — Label du domaine principal
        $secondaryDomain   — (optionnel) Label du domaine secondaire
        $activities        — array<string> des activités
        $href              — URL du profil (sinon CTA inactif)
--}}
@props([
    'name',
    'location'        => null,
    'primaryDomain'   => null,
    'secondaryDomain' => null,
    'activities'      => [],
    'href'            => null,
])

<article {{ $attributes->class('ds-card-artist group flex h-full w-full flex-col gap-8 bg-brand-paper px-8 py-10 shadow-[0_4px_16px_0_rgba(27,62,61,0.04)] transition hover:-translate-y-0.5 hover:shadow-[0_4px_24px_0_rgba(27,62,61,0.10)]') }}>
    {{-- Titre + lieu --}}
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-6">
            {{-- Titre --}}
            <h3 class="overflow-hidden text-ellipsis whitespace-nowrap font-serif text-2xl font-bold leading-tight text-brand">
                @if ($href)
                    <a href="{{ $href }}" class="hover:underline">{{ $name }}</a>
                @else
                    {{ $name }}
                @endif
            </h3>

            {{-- Lieu — dash line + texte léger --}}
            @if ($location)
                <div class="flex items-center gap-1.5">
                    <span class="inline-block h-px w-5 shrink-0 bg-brand" aria-hidden="true"></span>
                    <span class="text-sm font-light text-brand">{{ $location }}</span>
                </div>
            @endif
        </div>

        {{-- Domaines — bottom border, pas de <hr> séparé --}}
        @if ($primaryDomain || $secondaryDomain)
            <div class="flex flex-wrap gap-x-4 gap-y-2 border-b border-brand pb-6">
                @if ($primaryDomain)
                    <x-ds.tag variant="domain" domain-tone="primary">{{ $primaryDomain }}</x-ds.tag>
                @endif
                @if ($secondaryDomain)
                    <x-ds.tag variant="domain" domain-tone="secondary">{{ $secondaryDomain }}</x-ds.tag>
                @endif
            </div>
        @else
            <div class="border-b border-brand pb-6"></div>
        @endif
    </div>

    {{-- Activités --}}
    @if (! empty($activities))
        <ul class="flex flex-col gap-2">
            @foreach ($activities as $activity)
                <li class="flex items-center gap-1.5 text-sm text-brand">
                    <span class="inline-block h-px w-5 shrink-0 bg-brand" aria-hidden="true"></span>
                    <span class="font-medium">{{ $activity }}</span>
                </li>
            @endforeach
        </ul>
    @endif

    {{-- CTA — always pinned to the extreme bottom of the card --}}
    @if ($href)
        <x-ds.btn :href="$href" variant="primary" size="lg" class="mt-auto w-full">
            Voir profil
        </x-ds.btn>
    @else
        <x-ds.btn variant="primary" size="lg" class="mt-auto w-full" disabled>
            Voir profil
        </x-ds.btn>
    @endif
</article>

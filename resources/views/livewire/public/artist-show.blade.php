<div>
    {{-- OG / social meta tags --}}
    @push('head')
        @php
            $ogTitle       = $artist->name.' — Artistes.ne';
            $ogDescription = $artist->biography
                ? \Illuminate\Support\Str::limit(strip_tags($artist->biography), 160)
                : 'Découvrez le profil de '.$artist->name.' sur l\'inventaire des artistes neuchâtelois·es.';
            $ogImage       = $artist->cover_image
                ? \Illuminate\Support\Facades\Storage::url($artist->cover_image)
                : null;
            $ogUrl         = request()->url();
        @endphp
        <meta property="og:type"        content="profile" />
        <meta property="og:title"       content="{{ $ogTitle }}" />
        <meta property="og:description" content="{{ $ogDescription }}" />
        <meta property="og:url"         content="{{ $ogUrl }}" />
        @if ($ogImage)
            <meta property="og:image" content="{{ $ogImage }}" />
        @endif
        <meta name="twitter:card"        content="summary_large_image" />
        <meta name="twitter:title"       content="{{ $ogTitle }}" />
        <meta name="twitter:description" content="{{ $ogDescription }}" />
    @endpush

    {{-- Back + actions row (Figma 561:48688) --}}
    <x-ds.section variant="paper" padding="tight">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <a
                href="{{ route('public.artists.index') }}"
                wire:navigate
                class="inline-flex items-center gap-2 text-base font-medium text-brand transition hover:opacity-70"
            >
                <x-picto name="arrow-left" class="size-5" />
                <span class="underline-offset-2 hover:underline">Retour</span>
            </a>

            <div class="flex flex-wrap items-center gap-3">
                {{-- Share button with Alpine popover --}}
                @php
                    $shareUrl   = request()->url();
                    $shareTitle = $artist->name.' — Artistes.ne';
                    $fbUrl      = 'https://www.facebook.com/sharer/sharer.php?u='.urlencode($shareUrl);
                    $liUrl      = 'https://www.linkedin.com/sharing/share-offsite/?url='.urlencode($shareUrl);
                    $waUrl      = 'https://wa.me/?text='.urlencode($shareTitle.' '.$shareUrl);
                    $piUrl      = 'https://pinterest.com/pin/create/button/?url='.urlencode($shareUrl).'&description='.urlencode($shareTitle);
                    $emailUrl   = 'mailto:?subject='.rawurlencode($shareTitle).'&body='.rawurlencode($shareUrl);
                @endphp
                <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                    <button type="button"
                            @click="open = !open"
                            class="inline-flex h-11 items-center gap-2 border border-brand-muted bg-brand-paper px-4 text-sm font-medium text-brand transition hover:bg-brand-cream"
                            :aria-expanded="open.toString()">
                        <x-picto name="external-link" class="size-4 shrink-0" />
                        Partager mon profil
                    </button>

                    <div
                        x-show="open"
                        x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        class="absolute right-0 top-full z-30 mt-2 w-52 border border-brand-hairline bg-brand-paper shadow-lg"
                        role="menu"
                    >
                        <a href="{{ $fbUrl }}" target="_blank" rel="noopener" role="menuitem"
                           class="flex items-center gap-3 px-4 py-3 text-sm text-brand hover:bg-brand-cream transition-colors">
                            <x-picto name="facebook" set="social" class="size-4 shrink-0" /> Facebook
                        </a>
                        <a href="{{ $liUrl }}" target="_blank" rel="noopener" role="menuitem"
                           class="flex items-center gap-3 px-4 py-3 text-sm text-brand hover:bg-brand-cream transition-colors">
                            <x-picto name="linkedin" set="social" class="size-4 shrink-0" /> LinkedIn
                        </a>
                        <a href="{{ $piUrl }}" target="_blank" rel="noopener" role="menuitem"
                           class="flex items-center gap-3 px-4 py-3 text-sm text-brand hover:bg-brand-cream transition-colors">
                            <x-picto name="pinterest" set="social" class="size-4 shrink-0" /> Pinterest
                        </a>
                        <a href="{{ $waUrl }}" target="_blank" rel="noopener" role="menuitem"
                           class="flex items-center gap-3 px-4 py-3 text-sm text-brand hover:bg-brand-cream transition-colors">
                            <x-picto name="whatsapp" set="social" class="size-4 shrink-0" /> WhatsApp
                        </a>                        <a href="{{ $emailUrl }}" role="menuitem"
                           class="flex items-center gap-3 px-4 py-3 text-sm text-brand hover:bg-brand-cream transition-colors">
                            <x-picto name="email" class="size-4 shrink-0" /> E-mail
                        </a>
                        <div class="border-t border-brand-hairline">
                            <button type="button" role="menuitem"
                                    @click="navigator.clipboard.writeText('{{ $shareUrl }}').then(() => { $dispatch('notify', { message: 'Lien copié !' }); open = false; })"
                                    class="flex w-full items-center gap-3 px-4 py-3 text-sm text-brand hover:bg-brand-cream transition-colors">
                                <x-picto name="copy" class="size-4 shrink-0" /> Copier le lien
                            </button>
                        </div>
                    </div>
                </div>

                @if ($artist->display_contact_button && filled($artist->email))
                    <x-ds.btn variant="primary" size="md" icon="email" :href="'mailto:'.$artist->email">
                        Me contacter
                    </x-ds.btn>
                @endif
            </div>
        </div>
    </x-ds.section>

    {{-- Identity banner (Figma 561:48697 — fond paper, NON sombre) --}}
    <x-ds.section variant="paper" padding="tight">
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-[1fr_336px] lg:items-center lg:gap-16">
            {{-- Colonne gauche : nom + localisation + domaines + activités --}}
            <div class="flex flex-col gap-8">
                <div class="flex flex-col gap-4">
                    <h1 class="font-serif text-4xl font-bold leading-tight text-brand sm:text-5xl lg:text-[56px] lg:leading-[64px]">
                        {{ $artist->name }}
                    </h1>

                    {{-- Localisation (champ pas encore en base → masqué si absent) --}}
                    @if (filled($artist->city ?? null))
                        <p class="inline-flex items-center gap-2 text-base text-brand-muted">
                            <span aria-hidden="true">—</span>
                            <span>{{ $artist->city }}</span>
                        </p>
                    @endif

                    {{-- Domaines artistiques --}}
                    @if (filled($artist->discipline))
                        <div class="flex flex-wrap items-center gap-3">
                            <x-ds.tag variant="domain" domain-tone="primary">{{ $artist->discipline }}</x-ds.tag>
                            @if (filled($artist->secondary_discipline))
                                <x-ds.tag variant="domain" domain-tone="secondary">{{ $artist->secondary_discipline }}</x-ds.tag>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Tags activités --}}
                @php $activities = $artist->activities ?? []; @endphp
                @if (! empty($activities))
                    <div class="flex flex-wrap gap-2">
                        @foreach ($activities as $activity)
                            <x-ds.tag wire:key="activity-{{ $loop->index }}-{{ md5((string) $activity) }}" variant="primary">{{ $activity }}</x-ds.tag>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Colonne droite : portrait --}}
            <div class="w-full max-w-[336px] justify-self-start lg:justify-self-end">
                <div class="aspect-[4/5] w-full overflow-hidden bg-brand-hairline">
                    @if (filled($artist->cover_image))
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::url($artist->cover_image) }}"
                            alt="Portrait de {{ $artist->name }}"
                            class="size-full object-cover"
                        >
                    @else
                        <span class="flex h-full items-center justify-center font-serif text-7xl text-brand-muted/40">
                            {{ mb_substr($artist->name, 0, 1) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </x-ds.section>

    {{-- Description / bio (bande cream — Figma 561:48716) --}}
    @if (filled($artist->biography))
        <x-ds.section variant="cream" padding="default">
            <div class="prose prose-brand max-w-none text-base leading-relaxed text-brand sm:text-lg [&_a]:text-brand-teal [&_a]:underline [&_p+p]:mt-4">
                {!! $artist->biography !!}
            </div>
        </x-ds.section>
    @endif

    {{-- Sections profil (Figma 561:48718) --}}
    @php
        $personalLinks = collect($artist->links ?? [])
            ->filter(fn ($l) => ! empty($l['url'] ?? null))
            ->values();
        $collaborations = $artist->collaborations ?? [];
        $secondaryActivities = $artist->secondary_activities ?? [];
        $keywords = $artist->keywords ?? [];
        $hasAnySection = $personalLinks->isNotEmpty()
            || ! empty($collaborations)
            || ! empty($secondaryActivities)
            || ! empty($keywords);
    @endphp

    @if ($hasAnySection)
        <x-ds.section variant="paper" padding="tight">
            @if ($personalLinks->isNotEmpty())
                <x-ds.profile-section title="Espaces personnels">
                    @foreach ($personalLinks as $link)
                        <x-ds.link-list-item wire:key="personal-link-{{ $loop->index }}-{{ md5((string) ($link['url'] ?? '')) }}" :href="$link['url']">
                            {{ \Illuminate\Support\Str::of($link['url'])->replace(['https://', 'http://'], '')->rtrim('/') }}
                        </x-ds.link-list-item>
                    @endforeach
                </x-ds.profile-section>
            @endif

            @if (! empty($collaborations))
                <x-ds.profile-section title="Collaborations">
                    @foreach ($collaborations as $collab)
                        <x-ds.link-list-item wire:key="collaboration-{{ $loop->index }}-{{ md5((string) (($collab['name'] ?? '').'|'.($collab['url'] ?? ''))) }}" :href="$collab['url'] ?? null" :external="filled($collab['url'] ?? null)">
                            {{ $collab['name'] ?? $collab }}
                        </x-ds.link-list-item>
                    @endforeach
                </x-ds.profile-section>
            @endif

            @if (! empty($secondaryActivities))
                <x-ds.profile-section title="Activités secondaires">
                    <p class="text-base text-brand">
                        {{ implode(' / ', (array) $secondaryActivities) }}
                    </p>
                </x-ds.profile-section>
            @endif

            @if (! empty($keywords))
                <x-ds.profile-section title="Mots-clés" :divided="false">
                    <div class="flex flex-wrap gap-2">
                        @foreach ((array) $keywords as $keyword)
                            <x-ds.tag wire:key="keyword-{{ $loop->index }}-{{ md5((string) $keyword) }}" variant="primary">{{ $keyword }}</x-ds.tag>
                        @endforeach
                    </div>
                </x-ds.profile-section>
            @endif
        </x-ds.section>
    @endif

    {{-- Dernière mise à jour (Figma bottom row) --}}
    @if ($artist->updated_at)
        <x-ds.section variant="paper" padding="tight">
            <div class="flex items-center gap-2 text-sm text-brand-muted">
                <x-picto name="calendar" class="size-4" />
                <span>Dernière mise à jour : {{ $artist->updated_at->translatedFormat('j F Y') }}</span>
            </div>
        </x-ds.section>
    @endif
</div>

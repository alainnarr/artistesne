@props(['title' => 'Inventaire des artistes neuchâtelois·es'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=lora:600,600i,700,700i|public-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="flex min-h-screen flex-col bg-brand-paper font-sans text-brand antialiased">
    {{-- Header — Figma 561:49043 (desktop) / 561:49069 (tablet) / 561:49095 (mobile) --}}
    <header class="bg-brand text-brand-paper">
        <div class="mx-auto flex w-full max-w-[1280px] items-center justify-between px-4 py-4 sm:px-20 sm:py-6">
            <a href="{{ route('public.home') }}" class="inline-flex items-center text-brand-paper" aria-label="Artistes.ne" wire:navigate>
                <x-picto name="artistes-ne" set="logos" class="h-[15px] w-auto sm:h-[25px]" />
            </a>
            <nav class="flex items-center gap-4 sm:gap-12">
                <a
                    href="{{ route('public.about') }}"
                    class="text-base leading-[26px] sm:text-lg sm:leading-[30px] {{ request()->routeIs('public.about') ? 'border-b-[1.5px] border-brand-mint pb-0.5 text-brand-paper' : 'text-brand-paper transition hover:opacity-80' }}"
                    wire:navigate
                >À propos</a>
                <a
                    href="{{ route('artist.login') }}"
                    class="text-base leading-[26px] sm:text-lg sm:leading-[30px] {{ request()->routeIs('artist.*') ? 'border-b-[1.5px] border-brand-mint pb-0.5 text-brand-paper' : 'text-brand-paper transition hover:opacity-80' }}"
                    wire:navigate
                >Espace artistes</a>
            </nav>
        </div>
    </header>

    <main class="w-full flex-1">
        {{ $slot }}
    </main>

    <footer class="border-t border-brand-hairline bg-brand-paper">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-6 py-8 text-base text-brand sm:flex-row sm:px-20">
            <a href="https://www.ne.ch/" target="_blank" rel="noopener" aria-label="Canton de Neuchâtel" class="inline-flex items-center text-brand">
                <x-picto name="ne-dark" set="logos" class="h-8 w-auto" />
            </a>
            <div class="flex flex-wrap items-center justify-center gap-2">
                <span class="font-medium">© SCNE {{ now()->year }}</span>
                <span aria-hidden="true">|</span>
                <a href="{{ route('public.conditions') }}" class="underline underline-offset-2 hover:no-underline" wire:navigate>Conditions d'utilisation</a>
                <span aria-hidden="true">|</span>
                <a href="{{ route('public.privacy') }}" class="underline underline-offset-2 hover:no-underline" wire:navigate>Protection des données</a>
                <span aria-hidden="true">|</span>
                <a href="{{ route('public.contact') }}" class="underline underline-offset-2 hover:no-underline" wire:navigate>Contact</a>
            </div>
            <div class="flex items-center gap-6 text-brand">
                <a href="https://www.facebook.com/" target="_blank" rel="noopener" aria-label="Facebook" class="transition hover:opacity-70">
                    <x-picto name="facebook" set="social" class="size-6" />
                </a>
                <a href="https://www.instagram.com/" target="_blank" rel="noopener" aria-label="Instagram" class="transition hover:opacity-70">
                    <x-picto name="instagram" set="social" class="size-6" />
                </a>
            </div>
        </div>
    </footer>

    <x-ds.cookies-banner :link-href="route('public.about')" />

    @fluxScripts
    @stack('scripts')
</body>
</html>

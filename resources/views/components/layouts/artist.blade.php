@props(['title' => 'Espace artiste'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} — Inventaire des artistes neuchâtelois·es</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=lora:600,600i,700,700i|public-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-brand-paper font-sans text-brand antialiased">
    {{-- Header --}}
    <header class="bg-brand text-brand-paper">
        <div class="mx-auto flex w-full max-w-[1280px] items-center justify-between px-4 py-4 sm:px-20 sm:py-6">
            <a href="{{ route('artist.dashboard') }}" class="inline-flex items-center text-brand-paper" aria-label="Artistes.ne" wire:navigate>
                <x-picto name="artistes-ne" set="logos" class="h-[15px] w-auto sm:h-[25px]" />
            </a>
            <nav class="flex items-center gap-4 sm:gap-12">
                <a
                    href="{{ route('artist.dashboard') }}"
                    class="text-base leading-[26px] sm:text-lg sm:leading-[30px] {{ request()->routeIs('artist.dashboard') ? 'border-b-[1.5px] border-brand-mint pb-0.5 text-brand-paper' : 'text-brand-paper transition hover:opacity-80' }}"
                    wire:navigate
                >Tableau de bord</a>
                <a
                    href="{{ route('artist.profile.edit') }}"
                    class="text-base leading-[26px] sm:text-lg sm:leading-[30px] {{ request()->routeIs('artist.profile.*') ? 'border-b-[1.5px] border-brand-mint pb-0.5 text-brand-paper' : 'text-brand-paper transition hover:opacity-80' }}"
                    wire:navigate
                >Ma page</a>
                <form method="POST" action="{{ route('logout') }}" class="inline-flex">
                    @csrf
                    <button type="submit" class="text-base leading-[26px] text-brand-paper transition hover:opacity-80 sm:text-lg sm:leading-[30px]">
                        Déconnexion
                    </button>
                </form>
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
                <a href="{{ route('contact') }}" class="underline underline-offset-2 hover:no-underline" wire:navigate>Contact</a>
            </div>
            <div class="hidden sm:block"></div>
        </div>
    </footer>

    <x-ds.cookies-banner :link-href="route('about')" />

    @fluxScripts
    @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Bientôt disponible — Artistes.ne</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=lora:600|public-sans:400,500" rel="stylesheet" />
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-dvh flex-col bg-brand-cream font-sans antialiased">

    {{-- Décoration géométrique haut-droite --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -right-32 -top-32 size-[480px] rounded-full bg-brand-teal opacity-10 blur-3xl"></div>
        <div class="absolute -bottom-20 -left-20 size-[320px] rounded-full bg-brand-mint opacity-5 blur-3xl"></div>
    </div>

    <div class="relative flex flex-1 flex-col items-center justify-center px-6 py-20 text-center">

        {{-- Logo --}}
        <a href="https://artistes.ne.ch/" aria-label="Artistes.ne — Inventaire des artistes neuchâtelois·es" class="mb-14">
            <x-picto name="artistes-ne" set="logos" class="h-5 w-auto sm:h-7" />
        </a>

        {{-- Accroche principale --}}
        <div class="flex flex-col items-center gap-6">
            <h1 class="font-serif text-5xl font-semibold leading-tight text-brand sm:text-6xl">
                Bientôt<br>disponible
            </h1>
            <p class="max-w-xs text-base leading-relaxed text-brand-teal-light sm:max-w-sm sm:text-lg">
                L'inventaire des artistes neuchâtelois·es est en cours de finalisation.
                Revenez nous voir très prochainement.
            </p>
        </div>

        {{-- Séparateur --}}
        <span class="my-12 block h-px w-12 bg-brand-teal opacity-40"></span>

        {{-- Logo canton --}}
        <a href="https://www.ne.ch/" target="_blank" rel="noopener" aria-label="République et canton de Neuchâtel" class="opacity-40 transition-opacity hover:opacity-70 mt-4">
            <x-picto name="artistes-ne-dark" set="logos" class="h-9 w-auto" />
        </a>

    </div>

</body>
</html>

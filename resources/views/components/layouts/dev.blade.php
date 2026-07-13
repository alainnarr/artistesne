@props(['title' => 'Composants — Dev'])

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=lora:600,600i,700,700i|public-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen bg-zinc-100 font-sans text-brand antialiased">

    {{-- Sidebar nav --}}
    <aside class="fixed inset-y-0 left-0 flex w-56 flex-col gap-1 overflow-y-auto bg-brand px-4 py-6 text-brand-paper">
        <p class="mb-4 text-xs font-medium uppercase tracking-wider text-brand-paper/50">DS · Composants</p>

        @foreach ($sections ?? [] as $id => $label)
            <a href="#{{ $id }}"
               class="rounded px-3 py-1.5 text-sm text-brand-paper/80 transition hover:bg-brand-paper/10 hover:text-brand-paper">
                {{ $label }}
            </a>
        @endforeach
    </aside>

    {{-- Main content --}}
    <main class="ml-56 flex-1 px-10 py-10">
        <header class="mb-10 border-b border-zinc-300 pb-6">
            <h1 class="font-serif text-3xl font-bold text-brand">Galerie de composants</h1>
            <p class="mt-1 text-sm text-brand-muted">Aperçu des composants du design system — usage local uniquement.</p>
        </header>

        {{ $slot }}
    </main>

    @stack('scripts')
</body>
</html>

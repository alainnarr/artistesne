@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title . ' — ' : '' }}{{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=lora:600,600i,700,700i|public-sans:400,500,600" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col items-center justify-center bg-brand-cream font-sans text-brand antialiased">
    <div class="w-full max-w-md px-4 py-12">
        <div class="mb-8 text-center">
            <a href="{{ route('home') }}" wire:navigate>
                <x-picto name="artistes-ne" set="logos" class="mx-auto h-6 w-auto" />
            </a>
        </div>
        <div class="rounded bg-brand-paper p-8 shadow-sm">
            {{ $slot }}
        </div>
    </div>
    @livewireScripts
</body>
</html>

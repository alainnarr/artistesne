<x-layouts.public :title="__($exception->getMessage()) ?: 'Erreur'">
    @php
        $messages = [
            403 => ['title' => 'Accès', 'accent' => 'refusé',        'lead' => "Vous n'avez pas l'autorisation d'accéder à cette page."],
            419 => ['title' => 'Session', 'accent' => 'expirée',      'lead' => 'Votre session a expiré. Veuillez recharger la page et réessayer.'],
            429 => ['title' => 'Trop de', 'accent' => 'requêtes',     'lead' => 'Vous avez effectué trop de requêtes. Veuillez patienter avant de réessayer.'],
            503 => ['title' => 'Service', 'accent' => 'indisponible', 'lead' => 'Le site est temporairement en maintenance. Revenez dans quelques instants.'],
        ];
        $code    = $exception->getStatusCode();
        $entry   = $messages[$code] ?? ['title' => 'Erreur', 'accent' => (string) $code, 'lead' => "Une erreur s'est produite. Veuillez réessayer."];
    @endphp

    <x-ds.section variant="paper">
        <x-ds.hero
            variant="page"
            :title="$entry['title']"
            :accent="$entry['accent']"
        >
            <x-slot:lead-slot>
                <p>{{ $entry['lead'] }}</p>
            </x-slot:lead-slot>
        </x-ds.hero>
    </x-ds.section>

    <x-ds.section variant="cream" padding="tight">
        <div class="flex flex-col items-center gap-6 text-center">
            <p class="font-serif text-6xl font-bold text-brand-hairline">{{ $code }}</p>
            <a
                href="{{ route('home') }}"
                class="inline-flex items-center gap-2 rounded-none bg-brand-mint px-5 py-3 text-base font-medium text-brand transition-colors hover:bg-brand-mint-hover"
                wire:navigate
            >
                <x-picto name="arrow-left" class="h-4 w-4" />
                Retour à l'accueil
            </a>
        </div>
    </x-ds.section>
</x-layouts.public>

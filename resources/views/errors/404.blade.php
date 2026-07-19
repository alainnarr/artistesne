<x-layouts.public title="Page introuvable">
    <x-ds.section variant="paper">
        <x-ds.hero
            variant="page"
            title="Page"
            accent="introuvable"
        >
            <x-slot:lead-slot>
                <p>La page que vous cherchez n'existe pas ou a été déplacée.</p>
            </x-slot:lead-slot>
        </x-ds.hero>
    </x-ds.section>

    <x-ds.section variant="cream" padding="tight">
        <div class="flex flex-col items-center gap-6 text-center">
            <p class="font-serif text-6xl font-bold text-brand-hairline">404</p>
            <a
                href="{{ route('public.home') }}"
                class="inline-flex items-center gap-2 rounded-none bg-brand-mint px-5 py-3 text-base font-medium text-brand transition-colors hover:bg-brand-mint-hover"
                wire:navigate
            >
                <x-picto name="arrow-left" class="h-4 w-4" />
                Retour à l'accueil
            </a>
        </div>
    </x-ds.section>
</x-layouts.public>

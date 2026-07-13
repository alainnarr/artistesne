@props([
    'current' => 1,
    'steps' => [],
])

{{--
    Indicateur d'étapes du formulaire de référencement.
    Chaque étape affiche une barre de progression (menthe si atteinte, grise sinon)
    et un libellé numéroté, avec une coche pour les étapes déjà validées.
--}}
<div {{ $attributes->merge(['class' => 'w-full pb-12']) }}>
    <div class="flex" aria-hidden="true">
        @foreach ($steps as $i => $label)
            @php $stepNumber = $i + 1; @endphp
            <div @class([
                'h-2 flex-1 transition-colors',
                'bg-brand-teal-light' => $current > $stepNumber,   {{-- validée --}}
                'bg-brand-mint'       => $current === $stepNumber,  {{-- active --}}
                'bg-brand-track'      => $current < $stepNumber,    {{-- à venir --}}
                'border-l border-brand-paper' => $stepNumber > 1,
            ])></div>
        @endforeach
    </div>

    <ol class="mt-4 flex text-[14px] leading-[24px]">
        @foreach ($steps as $i => $label)
            <li @class([
                'flex flex-1 items-center gap-4',
                'font-medium text-brand' => $current >= $i + 1,
                'text-brand-muted' => $current < $i + 1,
            ])>
                <span>{{ $i + 1 }}.{{ $label }}</span>
                @if ($current > $i + 1)
                    <x-picto name="check" class="size-4 text-brand-teal-light" />
                @endif
            </li>
        @endforeach
    </ol>
</div>

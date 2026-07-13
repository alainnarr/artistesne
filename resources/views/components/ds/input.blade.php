{{--
    DS · Input — Figma nodes 2269:16306 / 2272:11389

    États : default · hover · focus · error · success · disabled
    Reset des styles tiers (Flux/intl-tel) : composant 100 % natif.

    Props :
        $label        — libellé au-dessus du champ
        $description  — aide sous le champ
        $error        — message d'erreur (active l'état error)
        $success      — true active la coche verte
        $icon         — picto leading
        $iconTrailing — picto trailing (sans bouton)
        $type         — input type
        $name / $id   — attributs natifs
--}}
@props([
    'label'        => null,
    'description'  => null,
    'error'        => null,
    'success'      => false,
    'icon'         => null,
    'iconTrailing' => null,
    'type'         => 'text',
    'name'         => null,
    'id'           => null,
    'required'     => false,
])

@php
    $id ??= $name ? 'input-'.$name : 'input-'.uniqid();
    $hasError = filled($error);

    $stateBorder = $hasError
        ? 'border-error focus-within:border-error'
        : ($success
            ? 'border-success focus-within:border-success'
            : 'border-brand-muted focus-within:border-brand');
@endphp

<div class="ds-field flex w-full flex-col gap-2">
    @if ($label)
        <label for="{{ $id }}" class="text-xs font-medium text-brand">{{ $label }}@if ($required)<span aria-hidden="true"> *</span>@endif</label>
    @endif

    <div class="group relative flex h-14 w-full items-center gap-2 bg-brand-paper px-4 border {{ $stateBorder }} has-[input:disabled]:bg-brand-track has-[input:disabled]:border-brand-soft">
        @if ($icon)
            <x-picto :name="$icon" class="size-5 shrink-0 text-brand-muted" />
        @endif

        <input
            type="{{ $type }}"
            id="{{ $id }}"
            @if($name) name="{{ $name }}" @endif
            {{ $attributes->class('peer flex-1 min-w-0 bg-transparent text-base text-brand placeholder-brand-muted outline-none disabled:text-brand-muted disabled:cursor-not-allowed') }}
        />

        @if ($success && ! $hasError)
            <x-picto name="check" class="size-5 shrink-0 text-success" />
        @elseif ($iconTrailing)
            <x-picto :name="$iconTrailing" class="size-5 shrink-0 text-brand-muted" />
        @endif
    </div>

    @if ($hasError)
        <p class="text-xs font-medium text-error">{{ $error }}</p>
    @elseif ($description)
        <p class="text-xs font-light text-brand-muted">{{ $description }}</p>
    @endif
</div>

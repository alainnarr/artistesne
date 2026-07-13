{{--
    DS · Radio — Figma 2272:11389

    États : default / hover / selected / disabled
    Cercle : border mint ; quand sélectionné, point central brand-teal sur BG mint.
--}}
@props([
    'label' => null,
    'name'  => null,
    'id'    => null,
    'value' => null,
])

@php
    $id ??= $name ? 'radio-'.$name.'-'.uniqid() : 'radio-'.uniqid();
@endphp

<label for="{{ $id }}" class="ds-radio group inline-flex cursor-pointer items-center gap-3 select-none has-[input:disabled]:cursor-not-allowed">
    <span class="relative inline-flex size-5 shrink-0 items-center justify-center rounded-full bg-brand-paper border border-brand-mint transition-colors
                 group-hover:bg-brand-mint-soft
                 group-has-[input:checked]:bg-brand-mint group-has-[input:checked]:border-brand-mint
                 group-has-[input:disabled]:bg-brand-track group-has-[input:disabled]:border-brand-soft">
        <input
            type="radio"
            id="{{ $id }}"
            @if($name) name="{{ $name }}" @endif
            @if($value !== null) value="{{ $value }}" @endif
            {{ $attributes->class('peer absolute inset-0 opacity-0 cursor-pointer disabled:cursor-not-allowed') }}
        />
        <span class="pointer-events-none size-2 rounded-full bg-brand-teal opacity-0 transition-opacity group-has-[input:checked]:opacity-100"></span>
    </span>
    @if ($label || $slot->isNotEmpty())
        <span class="text-base text-brand group-has-[input:disabled]:text-brand-muted">{{ $label ?? $slot }}</span>
    @endif
</label>

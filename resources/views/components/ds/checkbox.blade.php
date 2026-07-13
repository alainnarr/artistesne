{{--
    DS · Checkbox — Figma 2272:11389

    États : default / hover / checked / disabled
    Picto coché : check brand-teal sur fond mint.
--}}
@props([
    'label' => null,
    'name'  => null,
    'id'    => null,
    'value' => '1',
])

@php
    $id ??= $name ? 'cb-'.$name : 'cb-'.uniqid();
@endphp

<label for="{{ $id }}" class="ds-checkbox group inline-flex cursor-pointer items-center gap-3 select-none has-[input:disabled]:cursor-not-allowed">
    <span class="relative inline-flex size-5 shrink-0 items-center justify-center bg-brand-paper border border-brand-mint transition-colors
                 group-hover:bg-brand-mint-soft
                 peer-focus-visible:border-brand
                 group-has-[input:checked]:bg-brand-mint group-has-[input:checked]:border-brand-mint
                 group-has-[input:disabled]:bg-brand-track group-has-[input:disabled]:border-brand-soft">
        <input
            type="checkbox"
            id="{{ $id }}"
            @if($name) name="{{ $name }}" @endif
            value="{{ $value }}"
            {{ $attributes->class('peer absolute inset-0 opacity-0 cursor-pointer disabled:cursor-not-allowed') }}
        />
        <x-picto name="check" class="pointer-events-none size-3.5 text-brand-teal opacity-0 transition-opacity group-has-[input:checked]:opacity-100" />
    </span>
    @if ($label || $slot->isNotEmpty())
        <span class="text-base text-brand group-has-[input:disabled]:text-brand-muted">{{ $label ?? $slot }}</span>
    @endif
</label>

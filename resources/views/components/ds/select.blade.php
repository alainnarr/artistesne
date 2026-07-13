{{--
    DS · Select — Figma 2272:12569 / 2269:16306
    Native <select> stylé pour rester accessible, avec caret picto custom.

    Props : label, description, error, placeholder, name, id, options (array clé=>label) ou slot <option>
--}}
@props([
    'label'       => null,
    'description' => null,
    'error'       => null,
    'placeholder' => null,
    'name'        => null,
    'id'          => null,
    'options'     => [],
])

@php
    $id ??= $name ? 'select-'.$name : 'select-'.uniqid();
    $hasError = filled($error);
    $borderState = $hasError
        ? 'border-error focus-within:border-error'
        : 'border-brand-muted focus-within:border-brand';
@endphp

<div class="ds-field flex w-full flex-col gap-2">
    @if ($label)
        <label for="{{ $id }}" class="text-xs font-medium text-brand">{{ $label }}</label>
    @endif

    <div class="group relative flex h-14 w-full items-center bg-brand-paper border {{ $borderState }} has-[select:disabled]:bg-brand-track has-[select:disabled]:border-brand-soft">
        <select
            id="{{ $id }}"
            @if($name) name="{{ $name }}" @endif
            {{ $attributes->class('peer h-full w-full appearance-none bg-transparent pl-4 pr-12 text-base text-brand outline-none disabled:text-brand-muted disabled:cursor-not-allowed') }}
        >
            @if ($placeholder)
                <option value="" disabled selected hidden>{{ $placeholder }}</option>
            @endif
            @if (! empty($options))
                @foreach ($options as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            @else
                {{ $slot }}
            @endif
        </select>
        <x-picto name="caret-down" class="pointer-events-none absolute right-4 size-4 text-brand-muted" />
    </div>

    @if ($hasError)
        <p class="text-xs font-medium text-error">{{ $error }}</p>
    @elseif ($description)
        <p class="text-xs font-light text-brand-muted">{{ $description }}</p>
    @endif
</div>

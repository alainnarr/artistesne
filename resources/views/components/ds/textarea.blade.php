{{--
    DS · Textarea — Figma 2269:16306 (variant "Champ commentaire")
--}}
@props([
    'label'       => null,
    'description' => null,
    'error'       => null,
    'rows'        => 4,
    'name'        => null,
    'id'          => null,
    'required'    => false,
])

@php
    $id ??= $name ? 'textarea-'.$name : 'textarea-'.uniqid();
    $hasError = filled($error);
    $borderState = $hasError
        ? 'border-error focus-within:border-error'
        : 'border-brand-muted focus-within:border-brand';
@endphp

<div class="ds-field flex w-full flex-col gap-2">
    @if ($label)
        <label for="{{ $id }}" class="text-xs font-medium text-brand">{{ $label }}@if ($required)<span aria-hidden="true"> *</span>@endif</label>
    @endif

    <div class="group relative flex w-full bg-brand-paper border px-4 py-3 {{ $borderState }} has-[textarea:disabled]:bg-brand-track has-[textarea:disabled]:border-brand-soft">
        <textarea
            id="{{ $id }}"
            @if($name) name="{{ $name }}" @endif
            rows="{{ $rows }}"
            {{ $attributes->class('peer flex-1 min-w-0 bg-transparent text-base text-brand placeholder-brand-muted outline-none resize-y disabled:text-brand-muted disabled:cursor-not-allowed') }}
        >{{ $slot }}</textarea>
    </div>

    @if ($hasError)
        <p class="text-xs font-medium text-error">{{ $error }}</p>
    @elseif ($description)
        <p class="text-xs font-light text-brand-muted">{{ $description }}</p>
    @endif
</div>

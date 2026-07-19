{{--
    DS · Field — Figma "Master Text field" (node 19:1484, file quwzQh6G272IDJtnfF7teN)

    Canonical text/select/textarea field: floating label that breaks the top
    border line once filled/focused, teal border + check icon on success, red
    border + label + X icon on error. Replaces the old label-above
    ds/input,select,textarea family (removed — no longer matches the DS).
--}}
@props([
    'label' => '',
    'type' => 'text',
    'as' => 'input',
    'required' => false,
    'description' => null,
    'rows' => 3,
    'maxlength' => null,
])

@php
    $model = $attributes->wire('model')->value();
    $hasError = $model ? $errors->has($model) : false;
    // Les icônes de validation ne sont pas affichées pour les dates (icône native),
    // les selects (chevron), ni les champs désactivés (rien à valider).
    $hasGutter = $type !== 'date' && $as !== 'select' && ! $attributes->get('disabled');
    $showError = $hasError && $hasGutter;
    $showValid = ! $hasError && $hasGutter;
    // Les dates affichent toujours leur étiquette flottante (l'input natif montre « mm/jj/aaaa »),
    // mais la bordure ne devient verte que lorsqu'une valeur est réellement saisie.
    $alwaysFloat = $type === 'date';

    $fieldId = 'field-'.\Illuminate\Support\Str::slug($model ?: $label);

    // Position de l'étiquette au repos (centrée pour input, en haut pour textarea).
    $restingClass = $as === 'textarea'
        ? 'top-4 text-base'
        : 'top-1/2 -translate-y-1/2 text-base';

    // Position verticale de l'indicateur dans la gouttière droite.
    $gutterTop = $as === 'textarea' ? 'top-4' : 'top-1/2 -translate-y-1/2';

    // Tout ce qui n'est pas explicitement consommé ci-dessus (wire:*, mais
    // aussi disabled/autofocus/name/class/maxlength natifs) doit atteindre le
    // vrai contrôle, pas seulement les attributs wire:.
    $controlAttributes = $attributes->except(['class']);
@endphp

<div
    x-data="{ filled: false, focused: false }"
    x-init="
        filled = ($refs.control?.value ?? '') !== '';
        requestAnimationFrame(() => { filled = ($refs.control?.value ?? '') !== ''; });
    "
    {{ $attributes->only(['class'])->class(['relative flex flex-col gap-1']) }}
>
    <div class="relative">
        <div
            @if ($hasError)
                class="relative flex items-center rounded-[2px] border border-red-500 bg-brand-paper"
            @elseif ($attributes->get('disabled'))
                class="relative flex items-center rounded-[2px] border border-brand-muted bg-brand-track"
            @else
                x-bind:class="(filled || focused) ? 'border-brand-teal' : 'border-brand-muted'"
                class="relative flex items-center rounded-[2px] border bg-brand-paper transition-colors"
            @endif
        >
            @if ($as === 'textarea')
                <textarea
                    id="{{ $fieldId }}"
                    x-ref="control"
                    rows="{{ $rows }}"
                    @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                    placeholder=" "
                    x-on:focus="focused = true"
                    x-on:blur="focused = false"
                    x-on:input="filled = $event.target.value !== ''"
                    {{ $controlAttributes }}
                    class="peer w-full resize-y rounded-[2px] bg-transparent px-4 py-3 text-base text-brand placeholder-transparent focus:outline-none"
                ></textarea>
            @elseif ($as === 'select')
                <select
                    id="{{ $fieldId }}"
                    x-ref="control"
                    x-on:focus="focused = true"
                    x-on:blur="focused = false"
                    x-on:change="filled = $event.target.value !== ''"
                    {{ $controlAttributes }}
                    @disabled($attributes->get('disabled'))
                    class="peer h-14 w-full appearance-none rounded-[2px] bg-transparent px-4 pe-10 text-base text-brand focus:outline-none disabled:cursor-not-allowed disabled:text-brand-muted"
                >
                    <option value="" disabled></option>
                    {{ $slot }}
                </select>
                <x-picto name="caret-down" class="pointer-events-none absolute end-4 top-1/2 size-4 -translate-y-1/2 text-brand-muted" />
            @else
                <input
                    id="{{ $fieldId }}"
                    x-ref="control"
                    type="{{ $type }}"
                    placeholder=" "
                    x-on:focus="focused = true"
                    x-on:blur="focused = false"
                    x-on:input="filled = $event.target.value !== ''"
                    {{ $controlAttributes }}
                    class="peer h-14 w-full rounded-[2px] bg-transparent px-4 text-base text-brand placeholder-transparent focus:outline-none disabled:cursor-not-allowed disabled:text-brand-muted {{ $type === 'date' ? 'date-input pe-10' : '' }}"
                >

                @if ($type === 'date')
                    <x-picto name="calendar" class="pointer-events-none absolute end-4 top-1/2 size-5 -translate-y-1/2 text-brand-muted" />
                @endif
            @endif

            <label
                for="{{ $fieldId }}"
                x-bind:class="(filled || focused{{ $alwaysFloat ? ' || true' : '' }})
                    ? 'top-0 -translate-y-1/2 text-[12px] leading-[17px] font-medium {{ $hasError ? 'text-red-500' : 'text-brand-teal' }}'
                    : '{{ $restingClass }} text-[16px] leading-[24px] text-brand-muted'"
                class="pointer-events-none absolute start-3 bg-brand-paper px-1 transition-all duration-150"
            >
                {{ $label }}@if ($required)<span aria-hidden="true">*</span>@endif
            </label>
        </div>

        {{-- Indicateur de validation dans la gouttière, à droite du champ. --}}
        @if ($showError)
            <x-picto name="close" class="pointer-events-none absolute -end-7 {{ $gutterTop }} size-5 text-red-500" />
        @elseif ($showValid)
            <div x-show="filled" x-cloak class="pointer-events-none absolute -end-7 {{ $gutterTop }} text-brand-teal">
                <x-picto name="check" class="size-5" />
            </div>
        @endif
    </div>

    @if ($hasError)
        <p class="text-[12px] leading-[17px] font-medium text-red-600">{{ $errors->first($model) }}</p>
    @elseif ($description)
        <p class="text-[12px] leading-[17px] font-medium text-brand-muted">{{ $description }}</p>
    @endif
</div>

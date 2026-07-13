{{--
    DS · Datepicker — Figma node 2272:13659
    https://www.figma.com/design/quwzQh6G272IDJtnfF7teN/?node-id=2272-13659

    Calendrier custom propulsé par Alpine.js (locale fr-CH, format JJ.MM.AAAA).
    Un <input type="hidden"> conserve la valeur ISO (YYYY-MM-DD) pour le formulaire
    et reste compatible avec wire:model.

    Props :
        $label, $description, $error
        $name        — name de l'input hidden (et wire:model si fourni)
        $id          — id du wrapper
        $value       — date ISO YYYY-MM-DD initiale
        $min, $max   — bornes ISO YYYY-MM-DD
        $placeholder
        $wireModel   — nom du binding Livewire (optionnel)
--}}
@props([
    'label'       => null,
    'description' => null,
    'error'       => null,
    'name'        => null,
    'id'          => null,
    'value'       => null,
    'min'         => null,
    'max'         => null,
    'placeholder' => 'JJ.MM.AAAA',
    'wireModel'   => null,
])

@php
    $uid = $id ?? 'date-'.uniqid();
    $hasError = filled($error);
    $borderState = $hasError
        ? 'border-error focus-within:border-error'
        : 'border-brand-muted focus-within:border-brand';
@endphp

<div
    x-data="dsDatepicker({
        value: @js($value),
        min: @js($min),
        max: @js($max),
    })"
    @if($wireModel) x-modelable="iso" wire:model.live="{{ $wireModel }}" @endif
    class="ds-datepicker flex w-full flex-col gap-2"
>
    @if ($label)
        <label for="{{ $uid }}" class="text-xs font-medium text-brand">{{ $label }}</label>
    @endif

    <div class="relative">
        <div
            class="relative flex h-14 w-full items-center gap-2 bg-brand-paper px-4 border {{ $borderState }}"
            :class="{ 'border-brand': open && !@js($hasError) }"
        >
            <input
                type="text"
                id="{{ $uid }}"
                x-model="display"
                @input="onTextInput($event)"
                @focus="open = true"
                placeholder="{{ $placeholder }}"
                autocomplete="off"
                inputmode="numeric"
                {{ $attributes->class('peer flex-1 min-w-0 bg-transparent text-base text-brand placeholder-brand-muted outline-none') }}
            />
            @if($name)
                <input type="hidden" name="{{ $name }}" x-bind:value="iso">
            @endif
            <button
                type="button"
                @click="open = !open"
                class="cursor-pointer text-brand-muted hover:text-brand"
                aria-label="Ouvrir le calendrier"
            >
                <x-picto name="calendar" class="size-5 shrink-0" />
            </button>
        </div>

        {{-- Popover --}}
        <div
            x-show="open"
            x-cloak
            x-transition.opacity
            @click.outside="open = false"
            @keydown.escape.window="open = false"
            class="absolute left-0 top-full z-30 mt-1 w-[18rem] bg-brand-paper border border-brand-hairline shadow-[0_8px_24px_0_rgba(27,62,61,0.08)] p-4"
        >
        {{-- Header : navigation mois --}}
        <div class="flex items-center justify-between mb-3">
            <button type="button" @click="shiftMonth(-1)" class="p-1 text-brand-muted hover:text-brand" aria-label="Mois précédent">
                <x-picto name="caret-left" class="size-4" />
            </button>

            <div class="flex items-center gap-2">
                <select x-model.number="cursorMonth" class="text-sm font-medium text-brand bg-transparent outline-none cursor-pointer">
                    <template x-for="(name, i) in monthNames" :key="i">
                        <option :value="i" x-text="name"></option>
                    </template>
                </select>
                <select x-model.number="cursorYear" class="text-sm font-medium text-brand bg-transparent outline-none cursor-pointer">
                    <template x-for="y in yearRange" :key="y">
                        <option :value="y" x-text="y"></option>
                    </template>
                </select>
            </div>

            <button type="button" @click="shiftMonth(1)" class="p-1 text-brand-muted hover:text-brand" aria-label="Mois suivant">
                <x-picto name="caret-right" class="size-4" />
            </button>
        </div>

        {{-- Jours de la semaine --}}
        <div class="grid grid-cols-7 gap-1 mb-1">
            <template x-for="d in dayLabels" :key="d">
                <div class="text-center text-[10px] font-medium uppercase tracking-wider text-brand-muted py-1" x-text="d"></div>
            </template>
        </div>

        {{-- Grille jours --}}
        <div class="grid grid-cols-7 gap-1">
            <template x-for="cell in cells" :key="cell.key">
                <button
                    type="button"
                    @click="cell.disabled || cell.outside ? null : select(cell.date)"
                    :disabled="cell.disabled || cell.outside"
                    class="aspect-square text-sm flex items-center justify-center transition-colors"
                    :class="{
                        'text-brand-muted/30 cursor-default': cell.outside,
                        'text-brand-soft cursor-not-allowed': cell.disabled && !cell.outside,
                        'text-brand hover:bg-brand-mint-soft cursor-pointer': !cell.outside && !cell.disabled && !cell.selected,
                        'bg-brand-mint text-brand font-medium': cell.selected,
                        'ring-1 ring-brand-teal': cell.today && !cell.selected,
                    }"
                    x-text="cell.day"
                ></button>
            </template>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between mt-3 pt-3 border-t border-brand-hairline">
            <button type="button" @click="clear()" class="text-xs text-brand-muted hover:text-brand">
                Effacer
            </button>
            <button type="button" @click="selectToday()" class="text-xs font-medium text-brand-teal hover:text-brand-teal-hover">
                Aujourd'hui
            </button>
        </div>
    </div>
    </div>

    @if ($hasError)
        <p class="text-xs font-medium text-error">{{ $error }}</p>
    @elseif ($description)
        <p class="text-xs font-light text-brand-muted">{{ $description }}</p>
    @endif
</div>

@once
    @push('scripts')
    <script>
        window.dsDatepicker = function ({ value, min, max }) {
            const parseISO = (s) => {
                if (!s) return null;
                const [y, m, d] = s.split('-').map(Number);
                if (!y || !m || !d) return null;
                const dt = new Date(y, m - 1, d);
                return isNaN(dt) ? null : dt;
            };
            const toISO = (dt) => {
                if (!dt) return '';
                const y = dt.getFullYear();
                const m = String(dt.getMonth() + 1).padStart(2, '0');
                const d = String(dt.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            };
            const toDisplay = (dt) => {
                if (!dt) return '';
                const d = String(dt.getDate()).padStart(2, '0');
                const m = String(dt.getMonth() + 1).padStart(2, '0');
                return `${d}.${m}.${dt.getFullYear()}`;
            };

            const initial = parseISO(value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const minDate = parseISO(min);
            const maxDate = parseISO(max);

            return {
                open: false,
                iso: value || '',
                display: initial ? toDisplay(initial) : '',
                cursorYear: (initial ?? today).getFullYear(),
                cursorMonth: (initial ?? today).getMonth(),
                monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
                dayLabels: ['L', 'M', 'M', 'J', 'V', 'S', 'D'],

                get yearRange() {
                    const base = this.cursorYear;
                    const years = [];
                    for (let y = base - 10; y <= base + 10; y++) years.push(y);
                    return years;
                },

                get cells() {
                    const first = new Date(this.cursorYear, this.cursorMonth, 1);
                    // Monday-first offset (getDay: 0=Sun..6=Sat → shift to 0=Mon..6=Sun)
                    const offset = (first.getDay() + 6) % 7;
                    const daysInMonth = new Date(this.cursorYear, this.cursorMonth + 1, 0).getDate();
                    const cells = [];
                    const selected = parseISO(this.iso);

                    // Leading from previous month
                    const prevDays = new Date(this.cursorYear, this.cursorMonth, 0).getDate();
                    for (let i = offset - 1; i >= 0; i--) {
                        const day = prevDays - i;
                        const dt = new Date(this.cursorYear, this.cursorMonth - 1, day);
                        cells.push({ key: 'p' + day, day, date: dt, outside: true, disabled: true, selected: false, today: false });
                    }
                    // Current month
                    for (let day = 1; day <= daysInMonth; day++) {
                        const dt = new Date(this.cursorYear, this.cursorMonth, day);
                        const isDisabled = (minDate && dt < minDate) || (maxDate && dt > maxDate);
                        const isSelected = selected && dt.getTime() === selected.getTime();
                        const isToday = dt.getTime() === today.getTime();
                        cells.push({ key: 'c' + day, day, date: dt, outside: false, disabled: isDisabled, selected: isSelected, today: isToday });
                    }
                    // Trailing to fill 6 rows max → use enough to complete week
                    while (cells.length % 7 !== 0) {
                        const day = cells.length - offset - daysInMonth + 1;
                        const dt = new Date(this.cursorYear, this.cursorMonth + 1, day);
                        cells.push({ key: 'n' + day, day, date: dt, outside: true, disabled: true, selected: false, today: false });
                    }
                    return cells;
                },

                shiftMonth(delta) {
                    const dt = new Date(this.cursorYear, this.cursorMonth + delta, 1);
                    this.cursorYear = dt.getFullYear();
                    this.cursorMonth = dt.getMonth();
                },

                select(dt) {
                    this.iso = toISO(dt);
                    this.display = toDisplay(dt);
                    this.cursorYear = dt.getFullYear();
                    this.cursorMonth = dt.getMonth();
                    this.open = false;
                    this.$dispatch('input', this.iso);
                },

                selectToday() {
                    const t = new Date();
                    t.setHours(0, 0, 0, 0);
                    if ((minDate && t < minDate) || (maxDate && t > maxDate)) return;
                    this.select(t);
                },

                clear() {
                    this.iso = '';
                    this.display = '';
                    this.$dispatch('input', '');
                },

                onTextInput(e) {
                    const m = e.target.value.match(/^(\d{2})\.(\d{2})\.(\d{4})$/);
                    if (!m) return;
                    const dt = new Date(Number(m[3]), Number(m[2]) - 1, Number(m[1]));
                    if (!isNaN(dt)) {
                        this.iso = toISO(dt);
                        this.cursorYear = dt.getFullYear();
                        this.cursorMonth = dt.getMonth();
                    }
                },
            };
        };
    </script>
    @endpush
@endonce

{{-- Gallery card wrapper used in the component gallery --}}
@props(['label' => ''])

<div {{ $attributes->class(['rounded-sm border border-zinc-200 bg-white']) }}>
    @if ($label)
        <div class="border-b border-zinc-200 bg-zinc-50 px-5 py-2">
            <span class="text-xs font-medium text-zinc-500">{{ $label }}</span>
        </div>
    @endif
    <div class="p-6">
        {{ $slot }}
    </div>
</div>

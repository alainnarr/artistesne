@props(['level' => 'h2'])

<{{ $level }} {{ $attributes->merge(['class' => 'font-serif text-[28px] font-bold leading-[30px] text-brand']) }}>
    {{ $slot }}
</{{ $level }}>

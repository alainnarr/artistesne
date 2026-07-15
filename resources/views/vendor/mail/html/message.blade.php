<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
<img src="{{ asset('img/mail/artistes-ne.svg') }}" alt="Artistes.ne" height="25" style="display:block;height:25px;width:auto;">
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
<img src="{{ asset('img/mail/ne-dark.svg') }}" alt="République et Canton de Neuchâtel" height="34" style="display:block;height:34px;width:auto;margin:0 auto 8px;">
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>

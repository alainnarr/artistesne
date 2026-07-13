<x-mail::message>
Bonjour {{ $recipientName }},

{!! nl2br(e($body)) !!}

---

*Ce message vous a été envoyé par l'équipe de gestion de l'Inventaire des artistes neuchâtelois·es.*
</x-mail::message>

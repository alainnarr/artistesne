{{--
    DS · Email layout — Figma node 2439:39306
    https://www.figma.com/design/quwzQh6G272IDJtnfF7teN/?node-id=2439-39306

    Layout pour les emails transactionnels Artistes.ne.
    Header : badge brand sombre "Artistes.ne".
    Footer : logo //ne.ch.

    À utiliser avec @component('mail::message', …) ou comme template Blade direct.

    Props :
        $preheader — texte caché d'aperçu dans la boîte mail
--}}
@props(['preheader' => null])

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Artistes.ne</title>
    <style>
        body { margin: 0; padding: 0; background: #fefcf7; font-family: 'Public Sans', Arial, sans-serif; color: #2e3d3c; }
        .wrap { max-width: 600px; margin: 0 auto; padding: 24px 16px; }
        .card { background: #fefefe; padding: 32px 24px; }
        .header { display: inline-block; background: #2e3d3c; color: #fefefe; font-family: 'Lora', Georgia, serif; font-weight: 700; font-size: 20px; padding: 8px 16px; }
        .h1 { font-family: 'Lora', Georgia, serif; font-weight: 700; font-size: 24px; color: #2e3d3c; margin: 24px 0 8px; }
        .accent { color: #477e7b; font-style: italic; }
        .body { font-size: 16px; line-height: 24px; color: #2e3d3c; }
        .btn { display: inline-block; background: #bfeceb; color: #2e3d3c; padding: 12px 24px; font-weight: 500; text-decoration: none; }
        .footer { text-align: center; padding: 24px 0; font-size: 14px; color: #5f6665; }
    </style>
</head>
<body>
    @if ($preheader)
        <span style="display:none !important; visibility:hidden; opacity:0; color:transparent; height:0; width:0;">{{ $preheader }}</span>
    @endif

    <div class="wrap">
        <div class="card">
            <div class="header">Artistes.ne</div>
            {{ $slot }}
        </div>
        <div class="footer">
            <strong>//ne.ch</strong> · République et Canton de Neuchâtel
        </div>
    </div>
</body>
</html>

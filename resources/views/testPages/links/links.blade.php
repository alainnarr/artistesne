<!DOCTYPE html>
<html>

<head>
    <title>Links Test</title>
</head>

<body>

<h1>Links do Artist #{{ $artist->id }}</h1>

<h2>Criar Link</h2>

<form method="POST" action="/test-links">
    @csrf

    <input
        type="text"
        name="link"
        placeholder="https://example.com"
        required
    >

    <button type="submit">
        Criar
    </button>
</form>

<hr>

<h2>Links</h2>

@foreach($artist->links as $link)

    <p>
        {{ $link->link }}

        <form method="POST" action="/test-links" style="display:inline">
            @csrf
            @method('DELETE')

            <input
                type="hidden"
                name="link"
                value="{{ $link->link }}"
            >

            <button>
                Excluir
            </button>
        </form>
    </p>

@endforeach

<hr>

<h2>Atualizar Link</h2>

<form method="POST" action="/test-links">
    @csrf
    @method('PUT')

    <input
        type="text"
        name="old_link"
        placeholder="Link atual"
        required
    >

    <input
        type="text"
        name="new_link"
        placeholder="Novo link"
        required
    >

    <button>
        Atualizar
    </button>
</form>

</body>
</html>

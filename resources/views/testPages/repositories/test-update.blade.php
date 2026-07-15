<!-- TODO : Remove this test controller when the repository service is fully integrated into the application.-->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Teste Update Repository</title>
</head>
<body>

<h2>Atualizar Repository</h2>

<form
    action="{{ route('repositories.test-update') }}"
    method="POST"
    enctype="multipart/form-data"
>
    @csrf

    <div>
        <label>ID do Repository:</label>
        <input
            type="number"
            name="repository_id"
            required
        >
    </div>

    <br>

    <div>
        <label>Novo arquivo:</label>
        <input
            type="file"
            name="file"
            required
        >
    </div>

    <br>

    <button type="submit">
        Atualizar
    </button>

</form>


@if(session('success'))
    <p style="color: green">
        {{ session('success') }}
    </p>
@endif


@if($errors->any())
    <ul style="color:red">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif


</body>
</html>

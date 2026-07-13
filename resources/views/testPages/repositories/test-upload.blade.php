<!-- TODO : Remove this test controller when the repository service is fully integrated into the application.-->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Teste Upload Repository</title>
</head>
<body>

<h2>Teste Upload Repository</h2>

<form
    action="{{ route('test-upload.store') }}"
    method="POST"
    enctype="multipart/form-data"
>
    @csrf

    <div>
        <label>Tipo:</label>
        <select name="repositoryable_type">
            <option value="Artist">Artist</option>
            <option value="Registration">Registration</option>
        </select>
    </div>

    <br>

    <div>
        <label>ID do registro:</label>
        <input
            type="number"
            name="repositoryable_id"
            value="1"
            required
        >
    </div>

    <br>

    <div>
        <label>Arquivos:</label>
        <input
            type="file"
            name="files[]"
            multiple
            required
        >
    </div>

    <br>

    <button type="submit">
        Enviar
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

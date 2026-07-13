<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Teste - Registration</title>
</head>
<body style="font-family: sans-serif; max-width: 700px; margin: 40px auto;">

    <h1>Teste - Nova Registration</h1>

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if (session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('test-registration.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <p><label>Nome real</label><br>
        <input type="text" name="real_name" value="{{ old('real_name') }}"></p>

        <p><label>Nome artístico</label><br>
        <input type="text" name="artist_name" value="{{ old('artist_name') }}"></p>

        <p><label>URL</label><br>
        <input type="text" name="url" value="{{ old('url') }}"></p>

        <p><label>Data de nascimento</label><br>
        <input type="date" name="birth_date" value="{{ old('birth_date') }}"></p>

        <p><label>Email</label><br>
        <input type="email" name="email" value="{{ old('email') }}"></p>

        <p><label>Telefone</label><br>
        <input type="text" name="phone" value="{{ old('phone') }}"></p>

        <p><label>Local de residência</label><br>
        <input type="text" name="residence_location" value="{{ old('residence_location') }}"></p>

        <p><label>Disciplina principal</label><br>
        <select name="discipline_main">
            <option value="">-- selecione --</option>
            @foreach ($disciplines as $discipline)
                <option value="{{ $discipline->id }}">{{ $discipline->name ?? $discipline->id }}</option>
            @endforeach
        </select></p>

        <p><label>Atividades</label><br>
        <select name="activities[]" multiple>
            @foreach ($activities as $activity)
                <option value="{{ $activity->id }}">{{ $activity->name ?? $activity->id }}</option>
            @endforeach
        </select></p>

        <p><label>Status</label><br>
        <select name="enum_status">
            @foreach (\App\Enums\RegistrationStatus::cases() as $status)
                <option value="{{ $status->value }}">{{ $status->name }}</option>
            @endforeach
        </select></p>

        <p><label>Documentos (opcional, múltiplos)</label><br>
        <input type="file" name="files[]" multiple></p>

        <button type="submit">Criar Registration</button>
    </form>

    <hr>

    <h2>Registrations criadas</h2>
    <ul>
        @forelse ($registrations as $registration)
            <li>
                #{{ $registration->id }} — {{ $registration->artist_name }}
                ({{ $registration->enum_status->name ?? $registration->enum_status }})
                — {{ $registration->repositories->count() }} doc(s)
                — {{ $registration->activities->count() }} atividade(s)

                <form action="{{ route('test-registration.status', $registration) }}" method="POST" style="display:inline;">
                    @csrf
                    <select name="enum_status">
                        @foreach (\App\Enums\RegistrationStatus::cases() as $status)
                            <option value="{{ $status->value }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit">Mudar status</button>
                </form>
            </li>
        @empty
            <li>Nenhuma registration ainda.</li>
        @endforelse
    </ul>

</body>
</html>

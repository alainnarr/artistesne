<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Test - Registration</title>
</head>
<body style="font-family: sans-serif; max-width: 700px; margin: 40px auto;">

    <h1>Test - New Registration</h1>

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

        <p><label>Real Name</label><br><input type="text" name="real_name" value="{{ old('real_name') }}"></p>

        <p><label>Artist Name</label><br><input type="text" name="artist_name" value="{{ old('artist_name') }}"></p>

        <p><label>Slug</label><br><input type="text" name="slug" value="{{ old('slug') }}"></p>

        <p><label>Birth Date</label><br><input type="date" name="birth_date" value="{{ old('birth_date') }}"></p>

        <p><label>Email</label><br><input type="email" name="email" value="{{ old('email') }}"></p>

        <p><label>Phone</label><br><input type="text" name="phone" value="{{ old('phone') }}"></p>

        <p><label>Residence Location</label><br><input type="text" name="residence_location" value="{{ old('residence_location') }}"></p>

        <p><label>Locality</label><br><input type="text" name="locality" value="{{ old('locality') }}"></p>

        <p><label>Main Discipline</label><br>
        <select name="discipline_main">
            <option value="">-- selecione --</option>
            @foreach ($disciplines as $discipline)
                <option value="{{ $discipline->id }}">{{ $discipline->name ?? $discipline->id }}</option>
            @endforeach
        </select></p>

        <p><label>Secondary Discipline</label><br>
        <select name="discipline_secondary">
            <option value="">-- selecione --</option>
            @foreach ($disciplines as $discipline)
                <option value="{{ $discipline->id }}">
                    {{ $discipline->name ?? $discipline->id }}
                </option>
            @endforeach
        </select></p>

        <p><label>Activities</label><br>
        <select name="activities[]" multiple>
            @foreach ($activities as $activity)
                <option value="{{ $activity->id }}">{{ $activity->name ?? $activity->id }}</option>
            @endforeach
        </select></p>

        <p><label>Canton Link</label><br><textarea name="canton_link" rows="3">{{ old('canton_link') }}</textarea></p>

        <p><label>Training</label><br><textarea name="training" rows="3">{{ old('training') }}</textarea></p>

        <p><label>Paid Work</label><br><textarea name="paid_work" rows="3">{{ old('paid_work') }}</textarea></p>

        <p><label>Recognition</label><br><textarea name="recognition" rows="3">{{ old('recognition') }}</textarea></p>

        <p><label>Recent Achievements</label><br><textarea name="recent_achievements" rows="3">{{ old('recent_achievements') }}</textarea></p>

        <p><label>Last Work</label><br><textarea name="last_work" rows="3">{{ old('last_work') }}</textarea></p>

        <p><label>Documents (optional, multiple)</label><br><input type="file" name="files[]" multiple></p>

        <h3>Links</h3>
        <p>
            <label>Website</label><br>
            <input type="hidden" name="links[0][enum_type]" value="website">
            <input type="text" name="links[0][link]" value="{{ old('links.0.link') }}">
        </p>

        <p>
            <label>Instagram</label><br>
            <input type="hidden" name="links[1][enum_type]" value="instagram">
            <input type="text" name="links[1][link]" value="{{ old('links.1.link') }}">
        </p>

        <p>
            <label>Facebook</label><br>
            <input type="hidden" name="links[2][enum_type]" value="facebook">
            <input type="text" name="links[2][link]" value="{{ old('links.2.link') }}">
        </p>

        <button type="submit">Create Registration</button>
    </form>

    <hr>

    <h2>Created Registrations</h2>
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
            <li>No registrations yet.</li>
        @endforelse
    </ul>

</body>
</html>

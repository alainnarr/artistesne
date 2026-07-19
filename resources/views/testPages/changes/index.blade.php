<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Test - Artist Change Request</title>
</head>
<body style="font-family: sans-serif; max-width: 700px; margin: 40px auto;">

<h1>Test - Artist Change Request</h1>

@if (session('success'))
    <p style="color: green;">Change Request created.</p>

    <pre>{{ json_encode(session('payload'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
@endif

@if ($errors->any())
    <ul style="color:red;">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form method="POST" enctype="multipart/form-data">
    @csrf

    <p><label>Artist</label><br>
        <select name="artist_id">
            @foreach($artists as $artist)
                <option value="{{ $artist->id }}">
                    {{ $artist->id }} - {{ $artist->artist_name }}
                </option>
            @endforeach
        </select>
    </p>

    <p><label>Artist Name</label><br><input type="text" name="artist_name"></p>

    <p><label>Email</label><br><input type="email" name="email"></p>

    <p><label>Phone</label><br><input type="text" name="phone"></p>

    <p><label>City</label><br><input type="text" name="city"></p>

    <p><label>Biography</label><br><textarea name="biography" rows="5"></textarea></p>

    <p><label>Main Discipline</label><br>
        <select name="discipline_main_id">
            <option value="">-- select --</option>
            @foreach ($disciplines as $discipline)
                <option value="{{ $discipline->id }}">
                    {{ $discipline->name ?? $discipline->id }}
                </option>
            @endforeach
        </select>
    </p>

    <p><label>Secondary Discipline</label><br>
        <select name="discipline_secondary">
            <option value="">-- select --</option>
            @foreach ($disciplines as $discipline)
                <option value="{{ $discipline->id }}">
                    {{ $discipline->name ?? $discipline->id }}
                </option>
            @endforeach
        </select>
    </p>

    <p><label>Show Contact</label><br>
        <select name="enum_show_contact">
            <option value="1">Show</option>
            <option value="0">Hide</option>
        </select>
    </p>

    <p><label>Activities</label><br>

        <select name="activities[]" multiple>
            @foreach($activities as $activity)
                <option value="{{ $activity->id }}">
                    {{ $activity->name ?? $activity->id }}
                </option>
            @endforeach
        </select>
    </p>

    <p><label>Keywords (comma separated)</label><br>
        <input type="text" name="keywords" id="keywords" placeholder="painting,oil,abstract">
    </p>

    <h3>Links</h3>
    <p><label>Website</label><br>
        <input type="hidden" name="links[0][enum_type]" value="website">
        <input type="text" name="links[0][link]">
    </p>

    <p><label>Instagram</label><br>
        <input type="hidden" name="links[1][enum_type]" value="instagram">
        <input type="text" name="links[1][link]">
    </p>

    <p>
        <label>Facebook</label><br>
        <input type="hidden" name="links[2][enum_type]" value="facebook">
        <input type="text" name="links[2][link]">
    </p>

    <p>
        <label>TikTok</label><br>
        <input type="hidden" name="links[3][enum_type]" value="tiktok">
        <input type="text" name="links[3][link]">
    </p>

    <p>
        <label>YouTube</label><br>
        <input type="hidden" name="links[4][enum_type]" value="youtube">
        <input type="text" name="links[4][link]">
    </p>

    <p>
        <label>Other</label><br>
        <input type="hidden" name="links[5][enum_type]" value="other">
        <input type="text" name="links[5][link]">
    </p>

    <p><label>Image</label><br><input type="file" name="image"></p>

    <button type="submit">Create Change Request</button>

</form>

<script>
document.querySelector("form").addEventListener("submit", function () {

    document
        .querySelectorAll(".keyword-hidden")
        .forEach(e => e.remove());

    document
        .getElementById("keywords")
        .value
        .split(",")
        .map(v => v.trim())
        .filter(v => v.length > 0)
        .forEach(keyword => {

            const input = document.createElement("input");

            input.type = "hidden";
            input.name = "keywords[]";
            input.value = keyword;
            input.className = "keyword-hidden";

            this.appendChild(input);
        });

});
</script>

</body>
</html>

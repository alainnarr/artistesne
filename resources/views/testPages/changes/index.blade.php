<!doctype html>
<html>
<head>
    <title>Artist Change Request Test</title>
</head>
<body>

<h1>Artist Change Request</h1>

@if(session('success'))
    <div style="padding:10px;background:#dff0d8;">
        <strong>Created!</strong>

        <pre>{{ json_encode(session('payload'), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@endif

<form method="POST" enctype="multipart/form-data">
    @csrf

    <p>
        <label>Artist</label><br>

        <select name="artist_id">
            @foreach($artists as $artist)
                <option value="{{ $artist->id }}">
                    {{ $artist->id }} - {{ $artist->artist_name }}
                </option>
            @endforeach
        </select>
    </p>

    <p>
        <label>Artist Name</label><br>
        <input type="text" name="artist_name">
    </p>

    <p>
        <label>Email</label><br>
        <input type="email" name="email">
    </p>

    <p>
        <label>Phone</label><br>
        <input type="text" name="phone">
    </p>

    <p>
        <label>City</label><br>
        <input type="text" name="city">
    </p>

    <p>
        <label>Biography</label><br>
        <textarea name="biography"></textarea>
    </p>

    <p>
        <label>Discipline Secondary</label><br>
        <input type="number" name="discipline_secondary">
    </p>

    <p>
        <label>Activities (IDs separated by commas)</label><br>
        <input
            type="text"
            id="activities"
            placeholder="1,2,5"
        >
    </p>

    <p>
        <label>Image</label><br>
        <input type="file" name="image">
    </p>

    <input type="hidden" name="activities[]" id="activities-hidden">

    <button type="submit">
        Create Change Request
    </button>

</form>

<script>
document.querySelector("form").addEventListener("submit", function () {

    const input = document.getElementById("activities");

    document.querySelectorAll(".activity-hidden").forEach(e => e.remove());

    input.value
        .split(",")
        .map(x => x.trim())
        .filter(Boolean)
        .forEach(id => {

            const hidden = document.createElement("input");

            hidden.type = "hidden";
            hidden.name = "activities[]";
            hidden.value = id;
            hidden.classList.add("activity-hidden");

            this.appendChild(hidden);
        });
});
</script>

</body>
</html>

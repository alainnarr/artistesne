<!doctype html>
<html>

<head>
    <title>Test Keywords Service</title>

    <style>
        body {
            font-family: Arial;
            margin:40px;
        }

        form{
            margin-bottom:20px;
            padding:15px;
            border:1px solid #ccc;
        }

        table{
            border-collapse:collapse;
        }

        td,th{
            border:1px solid #ddd;
            padding:8px;
        }
    </style>
</head>

<body>

<h1>Keywords Service Test</h1>

@if(session('success'))
    <div style="color:green">
        {{ session('success') }}
    </div>
@endif

<form method="POST" action="/test-keywords/attach">
    @csrf

    <h3>Attach</h3>

    <select name="artist_id">
        @foreach($artists as $artist)
            <option value="{{ $artist->id }}">
                {{ $artist->artist_name }}
            </option>
        @endforeach
    </select>

    <input
        type="text"
        name="label"
        placeholder="Keyword">

    <button type="submit">
        Attach
    </button>

</form>

<form method="POST" action="/test-keywords/detach">
    @csrf

    <h3>Detach</h3>

    <select name="artist_id">
        @foreach($artists as $artist)
            <option value="{{ $artist->id }}">
                {{ $artist->artist_name }}
            </option>
        @endforeach
    </select>

    <input
        type="text"
        name="label"
        placeholder="Keyword">

    <button type="submit">
        Detach
    </button>

</form>

<h2>Registered Keywords</h2>

<table>

    <tr>
        <th>ID</th>
        <th>Label</th>
        <th>Artists</th>
    </tr>

    @foreach($keywords as $keyword)

        <tr>

            <td>{{ $keyword->id }}</td>

            <td>{{ $keyword->label }}</td>

            <td>

                @foreach($keyword->artists as $artist)

                    {{ $artist->artist_name }}<br>

                @endforeach

            </td>

        </tr>

    @endforeach

</table>

</body>
</html>

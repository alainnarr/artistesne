<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Synonyms</title>

    <style>
        body {
            font-family: Arial;
            margin: 30px;
        }

        form {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 25px;
        }

        input, select, button {
            padding: 6px;
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, td, th {
            border: 1px solid #ccc;
        }

        td, th {
            padding: 8px;
        }
    </style>
</head>
<body>

<h1>Test Synonyms</h1>

@if(session('success'))
    <div style="color:green">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div style="color:red">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<hr>

<h2>Create</h2>

<form method="POST" action="{{ route('test-synonyms.store') }}">
    @csrf

    <div>
        <label>Activity</label><br>

        <select name="activity_id">
            @foreach($activities as $activity)
                <option value="{{ $activity->id }}">
                    {{ $activity->id }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label>Label</label><br>
        <input
            type="text"
            name="label"
            placeholder="New synonym"
        >
    </div>

    <button type="submit">
        Create
    </button>
</form>

<hr>

<h2>Edit</h2>

<form method="POST" action="{{ route('test-synonyms.update') }}">
    @csrf
    @method('PUT')

    <div>
        <label>Activity</label><br>

        <select name="activity_id">
            @foreach($activities as $activity)
                <option value="{{ $activity->id }}">
                    {{ $activity->id }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label>Current Label</label><br>
        <input
            type="text"
            name="old_label"
        >
    </div>

    <div>
        <label>New Label</label><br>
        <input
            type="text"
            name="new_label"
        >
    </div>

    <button type="submit">
        Update
    </button>
</form>

<hr>

<h2>Delete</h2>

<form method="POST" action="{{ route('test-synonyms.delete') }}">
    @csrf
    @method('DELETE')

    <div>
        <label>Activity</label><br>

        <select name="activity_id">
            @foreach($activities as $activity)
                <option value="{{ $activity->id }}">
                    {{ $activity->id }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label>Label</label><br>
        <input
            type="text"
            name="label"
        >
    </div>

    <button type="submit">
        Delete
    </button>
</form>

<hr>

<h2>Registered Synonyms</h2>

@foreach($activities as $activity)

    <h3>Activity {{ $activity->id }}</h3>

    @if($activity->synonyms->isEmpty())
        <p>No synonyms.</p>
    @else

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Label</th>
                </tr>
            </thead>

            <tbody>

            @foreach($activity->synonyms as $synonym)

                <tr>
                    <td>{{ $synonym->id }}</td>
                    <td>{{ $synonym->label }}</td>
                </tr>

            @endforeach

            </tbody>
        </table>

    @endif

@endforeach

</body>
</html>

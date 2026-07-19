<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Change Requests</title>
</head>
<body>

<h1>Artist Change Requests</h1>

@if(session('success'))
    <p style="color:green">
        {{ session('success') }}
    </p>
@endif

@foreach($requests as $request)

    <hr>

    <p>
        <strong>ID:</strong> {{ $request->id }}
    </p>

    <p>
        <strong>Artist:</strong>
        {{ $request->artist->artist_name }}
    </p>

    <p>
        <strong>Status:</strong>
        {{ $request->enum_status->value }}
    </p>

    <p>
        <strong>Payload:</strong>
    </p>

    <pre>{{ json_encode(json_decode($request->payload), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>

    <form action="{{ route('test-changes.requests.status', $request) }}" method="POST">
        @csrf

        <input
            type="hidden"
            name="status"
            value="{{ \App\Enums\ArtistChangeRequestStatus::APPROVED->value }}"
        >

        <button type="submit">
            Approve
        </button>
    </form>

    <br>

    <form action="{{ route('test-changes.requests.status', $request) }}" method="POST">
        @csrf

        <input
            type="hidden"
            name="status"
            value="{{ \App\Enums\ArtistChangeRequestStatus::REJECTED->value }}"
        >

        <input
            type="text"
            name="review_notes"
            placeholder="Review notes"
        >

        <button type="submit">
            Reject
        </button>
    </form>

@endforeach

</body>
</html>

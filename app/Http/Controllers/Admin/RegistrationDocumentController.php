<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArtistRegistrationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationDocumentController extends Controller
{
    public function __invoke(Request $request, ArtistRegistrationRequest $artistRegistrationRequest, int $index): StreamedResponse
    {
        $documents = $artistRegistrationRequest->documents ?? [];

        abort_if(! isset($documents[$index]), 404);

        $doc = $documents[$index];
        $path = $doc['path'] ?? null;

        abort_if(! $path || ! Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, $doc['name'] ?? basename($path));
    }
}

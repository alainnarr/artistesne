<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Database\Models\Registration;
use App\Database\Models\Repository;
use App\Http\Controllers\Controller;
use App\Services\RepositoriesService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationDocumentController extends Controller
{
    public function __construct(private readonly RepositoriesService $repositoriesService) {}

    public function download(Request $request, Registration $registration, Repository $repository): StreamedResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        abort_unless(
            $repository->repositoryable_type === Registration::class
                && $repository->repositoryable_id === $registration->id,
            404
        );

        abort_unless($repository->has_file, 404);

        return $this->repositoriesService->download($repository);
    }
}

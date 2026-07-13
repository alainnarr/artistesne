<?php

namespace App\Http\Controllers;

use App\Services\RepositoriesService;
use Illuminate\Http\Request;
use App\Models\Artist;

//TODO : Remove this test controller when the repository service is fully integrated into the application.
final class RepositoryTestController extends Controller
{

    public function index()
    {
        return view('testPages.repositories.test-upload');
    }

    public function store(Request $request) {
        $service = new RepositoriesService();

        $files = $request->file('files');
        $model = Artist::firstOrFail();

        if (count($files) === 1) {

            $service->create(
                $model,
                $files[0]
            );

        } else {

            $service->createMultiple(
                $model,
                $files
            );
        }


        return back()->with(
            'success',
            'Arquivos enviados com sucesso'
        );
    }

    public function updateForm()
    {
        return view('testPages.repositories.test-update');
    }

    public function update(Request $request, RepositoriesService $service) {
        $repository = $service->update($request->repository_id, $request->file('file'));

        return back()->with('success','Repository atualizado: ' . $repository->id);
    }
}

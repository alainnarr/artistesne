<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SynonymsService;
use App\Database\Models\Activity;

final class SynonymsTestController extends Controller
{
    public function index()
{
    return view('testPages.synonyms.index', [
        'activities' => Activity::with('synonyms')->get(),
    ]);
}

    public function store(Request $request)
    {
        $service = new SynonymsService();

        $activity = Activity::find($request->activity_id);

        $service->create($activity, $request->label);

        return back();
    }

    public function update(Request $request, SynonymsService $service)
    {
        $activity = Activity::find($request->activity_id);

        $service->update(
            $activity,
            $request->old_label,
            $request->new_label
        );

        return back();
    }

    public function destroy(Request $request, SynonymsService $service)
    {
        $activity = Activity::find($request->activity_id);

        $service->delete(
            $activity,
            $request->label
        );

        return back();
    }
}

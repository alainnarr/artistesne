<?php


namespace App\Http\Controllers;

use App\Database\Models\Activity;
use App\Database\Models\Discipline;
use App\Database\Models\Registration;
use App\Enums\RegistrationStatus;
use App\Services\RegistrationsService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use Throwable;

class TestRegistrationsController extends Controller
{
    public function __construct(private readonly RegistrationsService $registrationsService) {}

    public function index(): View
    {
        $disciplines = Discipline::all();
        $activities = Activity::all();
        $registrations = Registration::with(['disciplineMain', 'repositories', 'activities'])
            ->latest()
            ->get();

        return view('testPages.registrations.index', compact('disciplines', 'activities', 'registrations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->only([
            'real_name',
            'artist_name',
            'slug',
            'birth_date',
            'email',
            'phone',
            'residence_location',
            'locality',
            'canton_link',
            'discipline_main',
            'discipline_secondary',
            'training',
            'paid_work',
            'recognition',
            'recent_achievements',
            'last_work',
            'enum_status',
            'reviewed_at',
            'reviewed_by',
            'review_notes',
        ]);

        $data['files'] = $request->file('files', []);
        $data['activities'] = $request->input('activities', []);
        $data['links'] = $request->input('links', []);

        try {
            $registration = $this->registrationsService->create($data);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }

        return redirect()
            ->route('test-registration.index')
            ->with('success', "Registration #{$registration->id} created successfully!");
    }

    public function changeStatus(Request $request, Registration $registration): RedirectResponse
    {

        $request->validate([
            'enum_status' => 'required',
            'review_notes' => 'nullable|string',
        ]);

        $this->registrationsService->changeStatus(
            $registration,
            RegistrationStatus::from($request->input('enum_status')),
            $request->input('review_notes')
        );

        return redirect()
            ->route('test-registration.index')
            ->with('success', "Status of Registration #{$registration->id} updated!");
    }
}

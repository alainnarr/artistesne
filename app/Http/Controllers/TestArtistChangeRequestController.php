<?php

namespace App\Http\Controllers;

use App\Database\Models\Activity;
use App\Database\Models\Artist;
use App\Services\ArtistChangeRequestsService;
use Illuminate\Http\Request;
use App\Database\Models\Registration;
use App\Enums\ArtistStatus;
use App\Enums\ArtistShowContact;
use App\Enums\RegistrationStatus;
use App\Services\ActivitiesService;
use App\Database\Models\ArtistChangeRequest;
use App\Database\Models\Discipline;
use App\Enums\ArtistChangeRequestStatus;

class TestArtistChangeRequestController extends Controller
{
    public function index(ActivitiesService $activitiesService)
    {
        $disciplines = Discipline::all();
        $activities = Activity::all();

        $registration = Registration::updateOrCreate(['email' => 'test@test.com'], [
            'real_name' => 'Real Name',
            'artist_name' => 'Test',
            'slug' => 'ne.ch/test',
            'birth_date' => '2020/01/01',
            'email' => 'test@test.com',
            'phone' => '+41000000000',
            'residence_location' => 'Residence',
            'locality' => 'City',
            'canton_link' => 'Canton link',
            'discipline_main' => 1,
            'discipline_secondary' => 3,
            'training' => 'Training',
            'paid_work' => 'Paid work',
            'recognition' => 'Recognition',
            'recent_achievements' => 'Recent achievements',
            'last_work' => 'Last work',
            'enum_status' => RegistrationStatus::APPROVED->value,
        ]);

        $artist = Artist::updateOrCreate(['registration_id' => $registration->id], [
            'registration_id' => $registration->id,
            'user_id' => 1,
            'slug' => $registration->slug,
            'artist_name' => 'Test',
            'email' => $registration->email,
            'phone' => $registration->phone,
            'biography' => 'Biography',
            'city' => 'Neuchatel',
            'discipline_main' => 1,
            'discipline_secondary' => 3,
            'enum_status' => ArtistStatus::PUBLISHED->value,
            'enum_show_contact' => ArtistShowContact::SHOW->value,
        ]);

        $activitiesService->attach($artist, 1);
        $artists = Artist::orderBy('artist_name')->get();

        return view('testPages.changes.index', compact('disciplines', 'activities', 'artists'));
    }

    public function store(Request $request, ArtistChangeRequestsService $service)
    {
        $artist = Artist::findOrFail($request->artist_id);
        $changeRequest = $service->create($artist, $request->all());

        return back()->with([
            'success' => true,
            'id' => $changeRequest->id,
            'payload' => json_decode($changeRequest->payload, true),
        ]);
    }

    public function requests()
    {
        return view('testPages.changes.requests', [
            'requests' => ArtistChangeRequest::with('artist')
                ->latest()
                ->get(),
        ]);
    }

    public function changeStatus(
        ArtistChangeRequest $changeRequest,
        Request $request,
        ArtistChangeRequestsService $service
    ) {
        $service->changeStatus(
            $changeRequest,
            ArtistChangeRequestStatus::from($request->status),
            $request->review_notes
        );

        return back()->with('success', 'Status changed.');
    }
}

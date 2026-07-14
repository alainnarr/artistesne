<?php

namespace App\Http\Controllers;

use App\Database\Models\Artist;
use App\Services\ArtistChangeRequestsService;
use Illuminate\Http\Request;
use App\Database\Models\Registration;
use App\Enums\ArtistStatus;
use App\Enums\ArtistShowContact;
use App\Enums\RegistrationStatus;
use App\Services\ActivitiesService;

class ArtistChangeRequestTestController extends Controller
{
    public function index(ActivitiesService $activitiesService)
    {
        $registration = Registration::updateOrCreate([
            'real_name' => 'Real Name',
            'artist_name' => 'Test',
            'url' => 'ne.ch/test',
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

        $artist = Artist::updateOrCreate([
            'registration_id' => $registration->id,
            'user_id' => 1,
            'artist_name' => 'Test',
            'email' => 'test@test.com',
            'phone' => '+41000000000',
            'rep_image' => null,
            'biography' => 'Biography',
            'city' => 'Neuchatel',
            'discipline_secondary' => 3,
            'enum_status' => ArtistStatus::Published->value,
            'enum_show_contact' => ArtistShowContact::SHOW->value,
        ]);

        $activities = $activitiesService->attach($artist, 1);

        return view('testPages.changes.index', [
            'artists' => Artist::orderBy('artist_name')->get(),
        ]);
    }

    public function store(
        Request $request,
        ArtistChangeRequestsService $service
    ) {
        $artist = Artist::findOrFail($request->artist_id);

        $changeRequest = $service->create($artist, [
            'artist_name' => $request->artist_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'biography' => $request->biography,
            'city' => $request->city,
            'discipline_secondary' => $request->discipline_secondary,
            'activities' => $request->activities ?? [],
            'links' => $request->links ?? [],
            'image' => $request->file('image'),
        ]);

        return back()->with([
            'success' => true,
            'id' => $changeRequest->id,
            'payload' => json_decode($changeRequest->payload, true),
        ]);
    }
}

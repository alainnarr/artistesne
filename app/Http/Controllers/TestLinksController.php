<?php

namespace App\Http\Controllers;

use App\Database\Models\Registration;
use App\Database\Models\Artist;
use App\Enums\LinkType;
use App\Services\LinksService;
use Illuminate\Http\Request;
use App\Enums\ArtistStatus;
use App\Enums\ArtistShowContact;
use App\Enums\RegistrationStatus;

final class TestLinksController extends Controller
{
    public function index()
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

        Artist::updateOrCreate([
            'registration_id' => $registration->id,
            'user_id' => 1,
            'artist_name' => 'Test',
            'email' => 'test@test.com',
            'phone' => '+41000000000',
            'rep_image' => null,
            'biography' => 'Biography',
            'city' => 'Neuchatel',
            'discipline_secondary' => 3,
            'enum_status' => ArtistStatus::PUBLISHED->value,
            'enum_show_contact' => ArtistShowContact::SHOW->value,
        ]);

        $artist = Artist::first();

        return view('testPages.links.links', compact('artist'));
    }

    public function store(Request $request)
    {
        $service = new LinksService();

        $artist = Artist::first();

        $service->create($artist, $request->link,LinkType::WEBSITE);

        return back();
    }

    public function update(Request $request, LinksService $service)
    {
        $artist = Artist::first();

        $service->update(
            $artist,
            $request->old_link,
            $request->new_link
        );

        return back();
    }

    public function destroy(Request $request, LinksService $service)
    {
        $artist = Artist::first();

        $service->delete(
            $artist,
            $request->link
        );

        return back();
    }
}

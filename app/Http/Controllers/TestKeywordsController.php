<?php

namespace App\Http\Controllers;

use App\Database\Models\Registration;
use App\Database\Models\Artist;
use App\Database\Models\Keyword;
use App\Services\KeywordsService;
use Illuminate\Http\Request;
use App\Enums\ArtistStatus;
use App\Enums\ArtistShowContact;
use App\Enums\RegistrationStatus;

class TestKeywordsController extends Controller
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

        return view('testPages.keywords.index', [
            'artists' => Artist::orderBy('artist_name')->get(),
            'keywords' => Keyword::orderBy('label')->get(),
        ]);
    }

    public function attach(Request $request, KeywordsService $service)
    {
        $request->validate([
            'artist_id' => 'required|exists:artists,id',
            'label' => 'required|string'
        ]);

        $artist = Artist::findOrFail($request->artist_id);

        $service->attach($artist, $request->label);

        return back()->with('success', 'Keyword added.');
    }

    public function detach(Request $request, KeywordsService $service)
    {
        $request->validate([
            'artist_id' => 'required|exists:artists,id',
            'label' => 'required|string'
        ]);

        $artist = Artist::findOrFail($request->artist_id);

        $service->detach($artist, $request->label);

        return back()->with('success', 'Keyword removed.');
    }
}

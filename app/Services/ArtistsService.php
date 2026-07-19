<?php

namespace App\Services;

use App\Database\Models\Registration;
use App\Database\Models\User;
use App\Database\Models\Artist;
use App\Enums\ArtistStatus;
use App\Enums\ArtistShowContact;
use App\Database\Models\ArtistChangeRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class ArtistsService
{
    public function __construct(
        private readonly ActivitiesService $activitiesService,
        private readonly LinksService $linksService,
        private readonly RepositoriesService $repositoryService,
        private readonly KeywordsService $keywordsService
    ) {}

    public function create(Registration $registration, User $user, ArtistStatus $status = ArtistStatus::DRAFT): Artist
    {
        $data = [
            'registration_id' => $registration->id,
            'user_id' => $user->id,
            'slug' => $registration->slug,
            'artist_name' => $registration->name,
            'email' => $registration->email,
            'phone' => $registration->phone,
            'city' => $registration->city,
            'discipline_main' => $registration->discipline_main,
            'discipline_secondary' => $registration->discipline_secondary,
            'enum_status' => $status,
            'enum_show_contact' => ArtistShowContact::HIDE,
        ];

        $artist = Artist::where('registration_id', $registration->id)->first();
        if ($artist) {
            return $artist;
        }

        Validator::make($data, Artist::getRules(array_keys($data)))->validate();

        $activities = $registration->activities()->pluck('activities.id')->toArray();

        return DB::transaction(function () use ($data, $activities) {
            $artist = Artist::firstOrCreate(['registration_id' => $data['registration_id']], $data);

            if (isset($activities)) {
                $this->activitiesService->sync($artist, $activities);
            }

            return $artist->fresh(['activities']);
        });
    }

    public function update(Artist $artist, ArtistChangeRequest $changeRequest): Artist
    {
        $data = $changeRequest->payload;

        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }

        if (!is_array($data)) {
            $data = [];
        }

        $artistData = Arr::except($data, ['activities', 'links', 'keywords', 'image']);
        Validator::make($artistData, Artist::getRules(array_keys($artistData), ['id' => $artist->id,]))->validate();

        return DB::transaction(function () use ($changeRequest, $artist, $data, $artistData) {
            $artist->update($artistData);

            if (isset($data['activities'])) {
                $this->activitiesService->sync($artist, $data['activities']);
            }

            if (isset($data['links'])) {
                $this->linksService->sync($artist, $data['links']);
            }

            if (isset($data['keywords'])) {
                $this->keywordsService->sync($artist, $data['keywords']);
            }

            if (isset($data['image'])) {
                $this->repositoryService->replicateRepository($changeRequest->image, $artist);
            }

            $this->changeStatus($artist, ArtistStatus::PUBLISHED);

            return $artist->fresh(['activities', 'links', 'keywords', 'image']);
        });
    }

    public function changeStatus(Artist $artist, ArtistStatus $status): Artist {
        if ($artist->enum_status === $status) {
            return $artist;
        }

        if ($status === ArtistStatus::PUBLISHED && !$artist->image()->exists()) {
            throw new \Exception('Cannot publish artist without an associated image.');
        }

        $artistData['enum_status'] = $status;
        if ($status === ArtistStatus::PUBLISHED) {
            $artistData['last_confirmed_at'] = now();
            if ($artist->published_at === null) {
                $artistData['published_at'] = now();
            }
        }

        Validator::make($artistData, Artist::getRules(array_keys($artistData), $artist))->validate();
        $artist->update($artistData);

        return $artist->fresh();
    }
}

<?php

namespace App\Services;

use App\Database\Models\Artist;
use App\Database\Models\ArtistChangeRequest;
use App\Enums\ArtistChangeRequestStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ArtistChangeRequestsService
{
    public function __construct(
        private readonly RepositoriesService $repositoryService,
        private readonly ArtistsService $artistsService,
    ) {}

    public function create(Artist $artist, array $data): ArtistChangeRequest
    {
        $payload = [];

        $ignoredFields = [
            'registration_id',
            'user_id',
            'enum_status',
            'published_at',
            'confirmed_at',
            'reminded_at',
            'rep_image',
        ];

        $fields = array_diff(
            $artist->getFillable(),
            $ignoredFields
        );

        foreach ($fields as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            if ($artist->{$field} != $data[$field]) {
                $payload[$field] = $data[$field];
            }
        }

        if (!empty($data['image'])) {
            $payload['image'] = true;
        }

        if (array_key_exists('activities', $data) && $this->hasActivitiesChanged($artist, $data['activities'])) {
            $payload['activities'] = $data['activities'];
        }

        if (array_key_exists('links', $data) && $this->hasLinksChanged($artist, $data['links'])) {
            $payload['links'] = $data['links'];
        }

        if (array_key_exists('keywords', $data) && $this->hasKeywordsChanged($artist, $data['keywords'])) {
            $payload['keywords'] = $data['keywords'];
        }

        if (empty($payload)) {
            throw new RuntimeException('No changes detected for artist with ID: ' . $artist->id);
        }

        return DB::transaction(function () use ($artist, $payload, $data) {
            ArtistChangeRequest::query()
                ->where('artist_id', $artist->id)
                ->where('enum_status', ArtistChangeRequestStatus::CHANGES_REQUESTED)
                ->delete();

            $artistChangeRequest = ArtistChangeRequest::create([
                'artist_id' => $artist->id,
                'payload' => json_encode($payload),
                'enum_status' => ArtistChangeRequestStatus::PENDING,
            ]);

            if (!empty($data['image'])) {
                $repository = $this->repositoryService->create($artistChangeRequest, $data['image']);
                $payload['image'] = $repository->id;
            }

            return $artistChangeRequest->fresh(['image']);
        });
    }

    private function hasActivitiesChanged(Artist $artist, array $activities): bool
    {
        $current = $artist->activities()
            ->pluck('activities.id')
            ->sort()
            ->values()
            ->toArray();

        $new = collect($activities)
            ->sort()
            ->values()
            ->toArray();

        return $current !== $new;
    }

    private function hasLinksChanged(Artist $artist, array $links): bool
    {
        $current = $artist->links()
            ->get()
            ->map(fn ($link) => [
                'enum_type' => $link->enum_type->value,
                'link' => trim($link->link),
            ])
            ->sortBy([
                ['enum_type', 'asc'],
                ['link', 'asc'],
            ])
            ->values()
            ->toArray();

        $new = collect($links)
            ->map(fn ($link) => [
                'enum_type' => $link['enum_type'],
                'link' => trim($link['link']),
            ])
            ->sortBy([
                ['enum_type', 'asc'],
                ['link', 'asc'],
            ])
            ->values()
            ->toArray();

        return $current !== $new;
    }

    private function hasKeywordsChanged(Artist $artist, array $keywords): bool
    {
        $current = $artist->keywords()
            ->pluck('keywords.label')
            ->map(fn (string $label) => mb_strtolower(trim($label)))
            ->sort()
            ->values()
            ->toArray();

        $new = collect($keywords)
            ->map(fn (string $label) => mb_strtolower(trim($label)))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        return $current !== $new;
    }

    public function changeStatus(
        ArtistChangeRequest $changeRequest,
        ArtistChangeRequestStatus $status,
        ?string $reviewNotes = null
    ): ArtistChangeRequest {
        $reviewer = Auth::user();

        return DB::transaction(function () use ($changeRequest, $status, $reviewNotes, $reviewer) {
            $changeRequest->update([
                'enum_status' => $status,
                'reviewed_at' => now(),
                'reviewed_by' => $reviewer?->id,
                'review_notes' => $reviewNotes,
            ]);

            if ($status === ArtistChangeRequestStatus::APPROVED) {
                $this->artistsService->update($changeRequest->artist, $changeRequest);
            }

            return $changeRequest->fresh();
        });
    }
}

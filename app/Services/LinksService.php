<?php

namespace App\Services;

use App\Database\Models\Link;
use App\Database\Models\Registration;
use App\Database\Models\Artist;
use App\Enums\LinkType;

class LinksService
{
    public function create(Artist|Registration $owner, string $link, LinkType $type = LinkType::WEBSITE): Link
    {
        return $owner->links()->create([
            'link' => $link,
            'enum_type' => $type,
        ]);
    }

    public function createMultiple(Artist|Registration $owner, array $links, LinkType $type = LinkType::WEBSITE): array
    {
        $records = [];

        foreach ($links as $link) {
            $records[] = $this->create($owner, $link['link'], $link['enum_type'] ?? $type);
        }

        return $records;
    }

    public function update(Artist|Registration $owner, string $link, string $newLink): Link
    {
        $linkModel = $owner->links()->where('link', $link)->firstOrFail();

        $linkModel->update([
            'link' => $newLink,
        ]);

        return $linkModel;
    }

    public function delete(Artist|Registration $owner, string $link): void
    {
        $linkModel = $owner->links()->where('link', $link)->firstOrFail();
        $linkModel->delete();
    }

    public function sync(Artist|Registration $owner, array $links, LinkType $type = LinkType::WEBSITE): void
    {
        $existingLinks = $owner->links()->where('enum_type', $type)->pluck('link')->toArray();

        foreach ($existingLinks as $existingLink) {
            if (!in_array($existingLink, $links)) {
                $this->delete($owner, $existingLink);
            }
        }

        foreach ($links as $link) {
            if (!in_array($link, $existingLinks)) {
                $this->create($owner, $link, $type);
            }
        }
    }
}

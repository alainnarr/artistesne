<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Models\Artist;
use App\Database\Models\Link;
use App\Database\Models\Registration;
use App\Enums\LinkType;

class LinksService
{
    public function create(Artist|Registration $owner, string $link, LinkType $type = LinkType::OTHER): Link
    {
        return $owner->links()->create([
            'link' => $link,
            'enum_type' => $type,
        ]);
    }

    public function createMultiple(Artist|Registration $owner, array $links, LinkType $type = LinkType::OTHER): array
    {
        $records = [];

        foreach ($links as $link) {
            $records[] = $this->create($owner, $link, $type);
        }

        return $records;
    }

    public function update(Artist|Registration $owner, string $link, string $newLink): Link
    {
        $linkModel = $owner->links()->where('link', $link)->firstOrFail();

        $linkModel->update(['link' => $newLink]);

        return $linkModel;
    }

    public function delete(Artist|Registration $owner, string $link): void
    {
        $owner->links()->where('link', $link)->firstOrFail()->delete();
    }

    public function sync(Artist|Registration $owner, array $links, LinkType $type = LinkType::OTHER): array
    {
        $owner->links()->delete();

        return $this->createMultiple($owner, $links, $type);
    }
}

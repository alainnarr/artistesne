<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\Models\Artist;
use App\Database\Models\Link;
use App\Database\Models\Registration;
use App\Enums\LinkType;
use Illuminate\Support\Facades\Validator;

class LinksService
{
    public function create(Artist|Registration $owner, string $link, LinkType $type = LinkType::OTHER): Link
    {
        $data = ['link' => $link, 'enum_type' => $type];
        Validator::make($data, Link::getRules(array_keys($data)))->validate();

        return $owner->links()->create($data);
    }

    public function update(Link $link, string $newLink): Link
    {
        Validator::make(['link' => $newLink], Link::getRules(['link']))->validate();
        $link->update(['link' => $newLink]);
        return $link;
    }

    public function createMultiple(Artist|Registration $owner, array $links, LinkType $type = LinkType::COLLABORATION): array
    {
        $records = [];

        foreach ($links as $link) {
            $records[] = $this->create($owner, $link['link'], LinkType::from($link['enum_type'] ?? $type));
        }

        return $records;
    }

    public function sync(Artist|Registration $owner, array $links): void
    {
        $incoming = collect($links)
            ->map(fn ($item) => [
                'enum_type' => $item['enum_type'],
                'link' => $item['link'] ?? null,
            ])
            ->keyBy('enum_type');

        $existing = $owner->links->keyBy('enum_type');

        foreach ($incoming as $enumType => $data) {
            $current = $existing->get($enumType);

            if ($current) {
                if (blank($data['link'])) {
                    $current->delete();
                    $existing->forget($enumType);
                    continue;
                }

                if ($current->link !== $data['link']) {
                    $this->update($current, $data['link']);
                }

                $existing->forget($enumType);
                continue;
            }

            if (filled($data['link'])) {
                $this->create($owner, $data['link'], LinkType::from($enumType));
            }
        }

        $existing->each->delete();
    }
}

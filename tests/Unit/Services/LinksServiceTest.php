<?php

namespace Tests\Unit\Services;

use App\Database\Models\Artist;
use App\Database\Models\Link;
use App\Enums\LinkType;
use App\Services\LinksService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\Services\Traits\Trait_Seed;

class LinksServiceTest extends TestCase
{
    use RefreshDatabase;
    use Trait_Seed;

    private LinksService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new LinksService();
    }

    private function createArtist(): Artist
    {
        return $this->seedArtist();
    }

    public function testCreateCreatesLinkForOwner(): void
    {
        $artist = $this->createArtist();
        $link = $this->service->create($artist, 'https://instagram.com/test', LinkType::INSTAGRAM);

        $this->assertInstanceOf(Link::class, $link);
        $this->assertDatabaseHas('links', [
            'artist_id' => $artist->id,
            'link' => 'https://instagram.com/test',
            'enum_type' => LinkType::INSTAGRAM->value,
        ]);
    }

    public function testCreateMultipleCreatesSeveralLinks(): void
    {
        $artist = $this->createArtist();
        $links = $this->service->createMultiple($artist,
            [
                [
                    'link' => 'https://old-link.com',
                    'enum_type' => \App\Enums\LinkType::WEBSITE,
                ],
                [
                    'link' => 'https://example.com',
                    'enum_type' => \App\Enums\LinkType::WEBSITE,
                ],
                [
                    'link' => 'https://github.com',
                    'enum_type' => \App\Enums\LinkType::WEBSITE,
                ],
            ],
            LinkType::WEBSITE
        );

        $this->assertCount(3, $links);
        $this->assertDatabaseCount('links', 3);
    }

    public function testUpdateChangesExistingLink(): void
    {
        $artist = $this->createArtist();
        $this->service->create($artist, 'https://old.com', LinkType::WEBSITE);
        $link = $this->service->update(
            $artist,
            'https://old.com',
            'https://new.com'
        );

        $this->assertEquals('https://new.com', $link->link);
        $this->assertDatabaseHas('links', [
            'id' => $link->id,
            'link' => 'https://new.com',
        ]);
    }

    public function testDeleteRemovesLink(): void
    {
        $artist = $this->createArtist();
        $link = $this->service->create($artist, 'https://delete.com', LinkType::WEBSITE);
        $this->service->delete($artist, 'https://delete.com');

        $this->assertDatabaseMissing('links', [
            'id' => $link->id,
        ]);
    }

    public function testSyncCreatesNewLinks(): void
    {
        $artist = $this->seedArtist();
        $this->service->sync($artist, [
            'https://example.com',
            'https://github.com',
        ]);

        $this->assertDatabaseHas('links', ['link' => 'https://example.com']);
        $this->assertDatabaseHas('links', ['link' => 'https://github.com']);
        $this->assertDatabaseCount('links', 2);
    }

    public function testSyncDeletesRemovedLinks(): void
    {
        $artist = $this->seedArtist();
        $this->service->createMultiple($artist, [
            [
                'link' => 'https://old-link.com',
                'enum_type' => \App\Enums\LinkType::WEBSITE,
            ],
            [
                'link' => 'https://example.com',
                'enum_type' => \App\Enums\LinkType::WEBSITE,
            ],
            [
                'link' => 'https://github.com',
                'enum_type' => \App\Enums\LinkType::WEBSITE,
            ],
        ]);
        $this->service->sync($artist, ['https://example.com',]);
        $this->assertDatabaseHas('links', ['link' => 'https://example.com',]);
        $this->assertDatabaseMissing('links', ['link' => 'https://github.com',]);
        $this->assertDatabaseCount('links', 1);
    }

    public function testSyncKeepsExistingLinksWithoutDuplicating(): void
    {
        $artist = $this->seedArtist();
        $this->service->create($artist, 'https://example.com');
        $this->service->sync($artist, ['https://example.com']);

        $this->assertDatabaseCount('links', 1);
    }

    public function testSyncOnlyAffectsLinksOfSameType(): void
    {
        $artist = $this->seedArtist();
        $this->service->create($artist, 'https://example.com', LinkType::WEBSITE);
        $this->service->create($artist, 'https://twitter.com', LinkType::X);

        $this->service->sync($artist, ['https://github.com'], LinkType::WEBSITE);
        $this->assertDatabaseMissing('links', ['link' => 'https://example.com']);
        $this->assertDatabaseHas('links', ['link' => 'https://twitter.com']);
        $this->assertDatabaseHas('links', ['link' => 'https://github.com']);
    }
}

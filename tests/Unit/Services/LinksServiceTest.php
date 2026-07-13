<?php

namespace Tests\Unit\Services;

use App\Database\Models\Artist;
use App\Database\Models\Discipline;
use App\Database\Models\Link;
use App\Database\Models\Registration;
use App\Enums\ArtistShowContact;
use App\Enums\ArtistStatus;
use App\Enums\LinkType;
use App\Enums\RegistrationStatus;
use App\Enums\DisciplineType;
use App\Models\User;
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

        $links = $this->service->createMultiple(
            $artist,
            [
                'https://site1.com',
                'https://site2.com',
            ],
            LinkType::WEBSITE
        );

        $this->assertCount(2, $links);

        $this->assertDatabaseCount('links', 2);
    }

    public function testUpdateChangesExistingLink(): void
    {
        $artist = $this->createArtist();

        $this->service->create(
            $artist,
            'https://old.com',
            LinkType::WEBSITE
        );

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

        $link = $this->service->create(
            $artist,
            'https://delete.com',
            LinkType::WEBSITE
        );

        $this->service->delete(
            $artist,
            'https://delete.com'
        );

        $this->assertDatabaseMissing('links', [
            'id' => $link->id,
        ]);
    }
}

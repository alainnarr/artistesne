<?php

namespace Tests\Unit\Services;

use App\Database\Models\Artist;
use App\Database\Models\Keyword;
use App\Database\Models\KeywordArtist;
use App\Services\KeywordsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\Services\Traits\Trait_Seed;

class KeywordsServiceTest extends TestCase
{
    use RefreshDatabase;
    use Trait_Seed;

    private KeywordsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new KeywordsService();
    }

    private function makeArtist(): Artist
    {
        return $this->seedArtist();
    }

    public function testAttachCreatesKeywordAndRelation(): void
    {
        $artist = $this->makeArtist();
        $this->service->attach($artist, 'Rock');
        $keyword = Keyword::where('label', 'rock')->first();

        $this->assertDatabaseHas('keywords', ['label' => 'rock']);
        $this->assertDatabaseHas('keywords_artists', ['artist_id' => $artist->id, 'keyword_id' => $keyword->id]);
    }

    public function testAttachDoesNotDuplicateRecords(): void
    {
        $artist = $this->makeArtist();
        $this->service->attach($artist, 'Rock');
        $this->service->attach($artist, 'Rock');

        $this->assertEquals(1, Keyword::count());
        $this->assertEquals(1, KeywordArtist::count());
    }

    public function testDetachRemovesRelation(): void
    {
        $artist = $this->makeArtist();
        $this->service->attach($artist, 'Rock');

        $this->assertTrue($this->service->detach($artist, 'Rock'));
        $this->assertDatabaseMissing('keywords_artists', ['artist_id' => $artist->id]);
    }

    public function testDetachReturnsFalseWhenKeywordDoesNotExist(): void
    {
        $artist = $this->makeArtist();

        $this->assertFalse($this->service->detach($artist, 'Fictitious Keyword'));
    }

    public function testAttachMultiple(): void
    {
        $artist = $this->makeArtist();
        $this->service->attachMultiple($artist, ['Rock', 'Jazz', 'Pop']);

        $this->assertEquals(3, Keyword::count());
        $this->assertEquals(3, KeywordArtist::count());
    }

    public function testDetachMultiple(): void
    {
        $artist = $this->makeArtist();
        $this->service->attachMultiple($artist, ['Rock', 'Jazz', 'Pop']);
        $deleted = $this->service->detachMultiple($artist, ['Rock', 'Pop']);

        $this->assertEquals(2, $deleted);
        $this->assertEquals(1, KeywordArtist::count());
    }

    public function testDeleteKeyword(): void
    {
        $artist = $this->makeArtist();
        $this->service->attach($artist, 'Rock');
        $keyword = Keyword::first();

        $this->assertTrue($this->service->delete($keyword));
        $this->assertDatabaseMissing('keywords', ['id' => $keyword->id]);
        $this->assertDatabaseCount('keywords_artists', 0);
    }
}

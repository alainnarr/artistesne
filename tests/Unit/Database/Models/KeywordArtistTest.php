<?php

namespace Tests\Unit\Database;

use App\Database\Models\KeywordArtist;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeywordArtistTest extends TestCase
{
    use RefreshDatabase;

    private function makeModel(): KeywordArtist
    {
        return new KeywordArtist();
    }

    public function testGetTableReturnsTableName(): void
    {
        $this->assertEquals('keywords_artists', $this->makeModel()->getTable());
    }

    public function testGetFillableReturnsArray(): void
    {
        $this->assertEquals(['keyword_id', 'artist_id'], $this->makeModel()->getFillable());
    }

    public function testGetRulesReturnsAllRules(): void
    {
        $rules = KeywordArtist::getRules();

        $this->assertArrayHasKey('keyword_id', $rules);
        $this->assertArrayHasKey('artist_id', $rules);
        $this->assertCount(2, $rules);
    }

    public function testGetRulesFiltersFields(): void
    {
        $rules = KeywordArtist::getRules(['artist_id']);

        $this->assertEquals(['artist_id'], array_keys($rules));
    }

    public function testKeywordRelation(): void
    {
        $relation = $this->makeModel()->keyword();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('keyword_id', $relation->getForeignKeyName());
    }

    public function testArtistRelation(): void
    {
        $relation = $this->makeModel()->artist();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('artist_id', $relation->getForeignKeyName());
    }
}

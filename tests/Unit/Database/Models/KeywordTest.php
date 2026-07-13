<?php

namespace Tests\Unit\Database;

use App\Database\Models\Keyword;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeywordTest extends TestCase
{
    use RefreshDatabase;

    private function makeModel(): Keyword
    {
        return new Keyword();
    }

    public function testGetTableReturnsTableName(): void
    {
        $this->assertEquals('keywords', $this->makeModel()->getTable());
    }

    public function testGetFillableReturnsArray(): void
    {
        $this->assertEquals(['label'], $this->makeModel()->getFillable());
    }

    public function testGetRulesReturnsAllRules(): void
    {
        $rules = Keyword::getRules();

        $this->assertArrayHasKey('label', $rules);
        $this->assertCount(1, $rules);
    }

    public function testGetRulesFiltersFields(): void
    {
        $rules = Keyword::getRules(['label']);

        $this->assertEquals(['label'], array_keys($rules));
    }

    public function testGetRulesIgnoresUnknownFields(): void
    {
        $rules = Keyword::getRules(['label', 'unknown']);

        $this->assertEquals(['label'], array_keys($rules));
    }

    public function testArtistsRelation(): void
    {
        $relation = $this->makeModel()->artists();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
        $this->assertEquals('keywords_artists', $relation->getTable());
        $this->assertEquals('keyword_id', $relation->getForeignPivotKeyName());
        $this->assertEquals('artist_id', $relation->getRelatedPivotKeyName());
    }

    public function testKeywordsArtistsRelation(): void
    {
        $relation = $this->makeModel()->keywordsArtists();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('keyword_id', $relation->getForeignKeyName());
    }
}

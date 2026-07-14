<?php

namespace Tests\Unit\Database;

use App\Database\Models\Activity;
use App\Database\Models\ActivityArtist;
use App\Database\Models\Artist;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityArtistTest extends TestCase
{
    use RefreshDatabase;

    private function makeModel(): ActivityArtist
    {
        return new ActivityArtist();
    }

    public function testGetTableReturnsTableName(): void
    {
        $this->assertEquals('activities_artists', $this->makeModel()->getTable());
    }

    public function testGetFillableReturnsArray(): void
    {
        $this->assertEquals(['activity_id', 'artist_id'], $this->makeModel()->getFillable());
    }

    public function testGetUpdatableReturnsArray(): void
    {
        $this->assertEquals([], $this->makeModel()->getUpdatable());
    }

    public function testGetRulesReturnsValidationRules(): void
    {
        $rules = ActivityArtist::getRules();

        $this->assertCount(2, $rules);
        $this->assertEquals('required|integer|exists:activities,id', $rules['activity_id']);
        $this->assertEquals('required|integer|exists:artists,id', $rules['artist_id']);
    }

    public function testGetRulesFiltersByFields(): void
    {
        $rules = ActivityArtist::getRules(['activity_id']);

        $this->assertCount(1, $rules);
        $this->assertArrayHasKey('activity_id', $rules);
        $this->assertArrayNotHasKey('artist_id', $rules);
    }

    public function testGetRulesReturnsIntersectionOnlyForKnownFields(): void
    {
        $rules = ActivityArtist::getRules(['activity_id', 'unknown_field']);

        $this->assertCount(1, $rules);
        $this->assertArrayHasKey('activity_id', $rules);
        $this->assertArrayNotHasKey('unknown_field', $rules);
    }

    public function testActivityRelation(): void
    {
        $relation = $this->makeModel()->activity();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Activity::class, $relation->getRelated());
        $this->assertEquals('activity_id', $relation->getForeignKeyName());
    }

    public function testArtistsRelation(): void
    {
        $relation = $this->makeModel()->artists();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Artist::class, $relation->getRelated());
        $this->assertEquals('artist_id', $relation->getForeignKeyName());
    }
}

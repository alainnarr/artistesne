<?php

namespace Tests\Unit\Database\Models;

use App\Database\Models\Artist;
use App\Database\Models\ArtistChangeRequest;
use App\Database\Models\Repository;
use App\Enums\ArtistChangeRequestStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Validation\Rules\Enum;
use Tests\TestCase;
use App\Database\Models\User;

class ArtistChangeRequestTest extends TestCase
{
    private function makeModel(): ArtistChangeRequest
    {
        return new ArtistChangeRequest();
    }

    public function testGetTableReturnsTableName(): void
    {
        $this->assertEquals('artists_change_requests', $this->makeModel()->getTable());
    }

    public function testGetFillableReturnsArray(): void
    {
        $this->assertEquals([
            'artist_id',
            'payload',
            'enum_status',
            'reviewed_at',
            'reviewed_by',
            'review_notes',
        ], $this->makeModel()->getFillable());
    }

    public function testGetUpdatableReturnsArray(): void
    {
        $this->assertEquals([
            'enum_status',
            'reviewed_at',
            'reviewed_by',
            'review_notes',
        ], $this->makeModel()->getUpdatable());
    }

    public function testCastsReturnsEnumStatus(): void
    {
        $casts = $this->makeModel()->getCasts();

        $this->assertArrayHasKey('enum_status', $casts);
        $this->assertEquals(
            ArtistChangeRequestStatus::class,
            $casts['enum_status']
        );
    }

    public function testEnumStatusAttributeReturnsEnumInstance(): void
    {
        $model = $this->makeModel();
        $model->enum_status = ArtistChangeRequestStatus::PENDING->value;

        $this->assertInstanceOf(ArtistChangeRequestStatus::class, $model->enum_status);
        $this->assertEquals(ArtistChangeRequestStatus::PENDING, $model->enum_status);
    }

    public function testGetRulesReturnsValidationRules(): void
    {
        $rules = ArtistChangeRequest::getRules();

        $this->assertCount(6, $rules);
        $this->assertEquals('required|integer|exists:artists,id', $rules['artist_id']);
        $this->assertEquals('required|json', $rules['payload']);
        $this->assertIsArray($rules['enum_status']);
        $this->assertEquals('required', $rules['enum_status'][0]);
        $this->assertInstanceOf(Enum::class, $rules['enum_status'][1]);
        $this->assertEquals('nullable|date', $rules['reviewed_at']);
        $this->assertEquals('nullable|integer|exists:newusers,id', $rules['reviewed_by']);
        $this->assertEquals('nullable|string', $rules['review_notes']);
    }

    public function testGetRulesFiltersByFields(): void
    {
        $rules = ArtistChangeRequest::getRules(['payload', 'enum_status']);

        $this->assertCount(2, $rules);
        $this->assertArrayHasKey('payload', $rules);
        $this->assertArrayHasKey('enum_status', $rules);
    }

    public function testGetRulesReturnsIntersectionOnlyForKnownFields(): void
    {
        $rules = ArtistChangeRequest::getRules(['payload', 'unknown_field']);

        $this->assertCount(1, $rules);
        $this->assertArrayHasKey('payload', $rules);
        $this->assertArrayNotHasKey('unknown_field', $rules);
    }

    public function testArtistRelation(): void
    {
        $relation = $this->makeModel()->artist();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Artist::class, $relation->getRelated());
        $this->assertEquals('artist_id', $relation->getForeignKeyName());
    }

    public function testRepositoriesRelation(): void
    {
        $relation = $this->makeModel()->repositories();

        $this->assertInstanceOf(MorphMany::class, $relation);
        $this->assertInstanceOf(Repository::class, $relation->getRelated());
        $this->assertEquals('repositoryable_type', $relation->getMorphType());
    }

    public function testImageRelation(): void
    {
        $relation = $this->makeModel()->image();

        $this->assertInstanceOf(MorphOne::class, $relation);
        $this->assertInstanceOf(Repository::class, $relation->getRelated());
        $this->assertEquals('repositoryable_type', $relation->getMorphType());
    }

    public function testReviewedByRelation(): void
    {
        $relation = $this->makeModel()->reviewedBy();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(User::class, $relation->getRelated());
        $this->assertEquals('reviewed_by', $relation->getForeignKeyName());
    }
}

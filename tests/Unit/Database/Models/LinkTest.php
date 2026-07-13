<?php

namespace Tests\Unit\Database;

use App\Database\Models\Link;
use App\Database\Models\Artist;
use App\Database\Models\Registration;
use App\Enums\LinkType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkTest extends TestCase
{
    use RefreshDatabase;

    private function makeModel(): Link
    {
        return new Link();
    }

    public function testGetTableReturnsTableName(): void
    {
        $model = $this->makeModel();

        $this->assertEquals('links', $model->getTable());
    }

    public function testGetFillableReturnsArray(): void
    {
        $model = $this->makeModel();

        $this->assertEquals(['artist_id', 'registration_id', 'link', 'enum_type'], $model->getFillable());
    }

    public function testGetUpdatableReturnsArray(): void
    {
        $model = $this->makeModel();

        $this->assertEquals(['link'], $model->getUpdatable());
    }

    public function testGetRulesReturnsExpectedRules(): void
    {
        $rules = Link::getRules();

        $this->assertArrayHasKey('artist_id', $rules);
        $this->assertArrayHasKey('registration_id', $rules);
        $this->assertArrayHasKey('link', $rules);
        $this->assertArrayHasKey('enum_type', $rules);
    }

    public function testGetRulesCanFilterFields(): void
    {
        $rules = Link::getRules(['link']);

        $this->assertEquals(['link' => 'required|string|max:255'], $rules);
    }

    public function testArtistRelation(): void
    {
        $model = $this->makeModel();
        $relation = $model->artist();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals(Artist::class, $relation->getRelated()::class);
    }

    public function testRegistrationRelation(): void
    {
        $model = $this->makeModel();
        $relation = $model->registration();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals(Registration::class, $relation->getRelated()::class);
    }

    public function testEnumTypeCast(): void
    {
        $model = $this->makeModel();

        $casts = $model->getCasts();

        $this->assertEquals(LinkType::class, $casts['enum_type']);
    }
}

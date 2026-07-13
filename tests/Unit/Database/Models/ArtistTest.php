<?php

namespace Tests\Unit\Database;

use App\Database\Models\Activity;
use App\Database\Models\Artist;
use App\Database\Models\Discipline;
use App\Database\Models\Link;
use App\Database\Models\Registration;
use App\Database\Models\Repository;
use App\Enums\ArtistShowContact;
use App\Enums\ArtistStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\Rules\Enum;
use Tests\TestCase;

class ArtistTest extends TestCase
{
    use RefreshDatabase;

    private function makeModel(): Artist
    {
        return new Artist();
    }

    public function testGetTableReturnsTableName(): void
    {
        $model = $this->makeModel();

        $this->assertEquals('newartists', $model->getTable());
    }

    public function testGetFillableReturnsArray(): void
    {
        $model = $this->makeModel();

        $this->assertEquals([
            'registration_id',
            'user_id',
            'artist_name',
            'email',
            'phone',
            'rep_image',
            'biography',
            'city',
            'discipline_secondary',
            'enum_status',
            'enum_show_contact',
            'published_at',
            'confirmed_at',
            'reminded_at',
        ], $model->getFillable());
    }

    public function testGetUpdatableReturnsArray(): void
    {
        $model = $this->makeModel();

        $this->assertEquals([], $model->getUpdatable());
    }

    public function testGetRulesReturnsValidationRules(): void
    {
        $rules = Artist::getRules();

        $this->assertCount(14, $rules);
        $this->assertEquals('required|exists:registrations,id', $rules['registration_id']);
        $this->assertEquals('required|exists:users,id', $rules['user_id']);
        $this->assertEquals('required|string|max:255', $rules['artist_name']);
        $this->assertEquals('nullable|email|max:125', $rules['email']);
        $this->assertEquals('nullable|string|max:15', $rules['phone']);
        $this->assertEquals('nullable|exists:repositories,id', $rules['rep_image']);
        $this->assertEquals('nullable|string', $rules['biography']);
        $this->assertEquals('nullable|string|max:125', $rules['city']);
        $this->assertEquals('nullable|exists:disciplines,id', $rules['discipline_secondary']);
        $this->assertIsArray($rules['enum_status']);
        $this->assertEquals('required', $rules['enum_status'][0]);
        $this->assertInstanceOf(Enum::class, $rules['enum_status'][1]);
        $this->assertIsArray($rules['enum_show_contact']);
        $this->assertEquals('required', $rules['enum_show_contact'][0]);
        $this->assertInstanceOf(Enum::class, $rules['enum_show_contact'][1]);
        $this->assertEquals('nullable|date', $rules['published_at']);
        $this->assertEquals('nullable|date', $rules['confirmed_at']);
        $this->assertEquals('nullable|date', $rules['reminded_at']);
    }

    public function testGetRulesReturnsFilteredFields(): void
    {
        $rules = Artist::getRules(['artist_name']);

        $this->assertCount(1, $rules);
        $this->assertArrayHasKey('artist_name', $rules);
        $this->assertEquals('required|string|max:255', $rules['artist_name']);
    }

    public function testActivitiesRelation(): void
    {
        $relation = $this->makeModel()->activities();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
        $this->assertInstanceOf(Activity::class, $relation->getRelated());
        $this->assertEquals('activities_artists', $relation->getTable());
        $this->assertEquals('artist_id', $relation->getForeignPivotKeyName());
        $this->assertEquals('activity_id', $relation->getRelatedPivotKeyName());
    }

    public function testActivitiesArtistsRelation(): void
    {
        $relation = $this->makeModel()->activitiesArtists();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('artist_id', $relation->getForeignKeyName());
    }

    public function testDisciplineSecondaryRelation(): void
    {
        $relation = $this->makeModel()->disciplineSecondary();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Discipline::class, $relation->getRelated());
    }

    public function testRegistrationRelation(): void
    {
        $relation = $this->makeModel()->registration();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Registration::class, $relation->getRelated());
    }

    public function testUserRelation(): void
    {
        $relation = $this->makeModel()->user();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(User::class, $relation->getRelated());
    }

    public function testRepImageRelation(): void
    {
        $relation = $this->makeModel()->repImage();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Repository::class, $relation->getRelated());
    }

    public function testRepositoriesRelation(): void
    {
        $relation = $this->makeModel()->repositories();

        $this->assertInstanceOf(MorphMany::class, $relation);
        $this->assertInstanceOf(Repository::class, $relation->getRelated());
    }

    public function testLinksRelation(): void
    {
        $relation = $this->makeModel()->links();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf(Link::class, $relation->getRelated());
    }

    public function testCastsEnumStatusToArtistStatus(): void
    {
        $casts = $this->makeModel()->getCasts();

        $this->assertArrayHasKey('enum_status', $casts);
        $this->assertEquals(ArtistStatus::class, $casts['enum_status']);
    }

    public function testCastsEnumShowContactToArtistShowContact(): void
    {
        $casts = $this->makeModel()->getCasts();

        $this->assertArrayHasKey('enum_show_contact', $casts);
        $this->assertEquals(ArtistShowContact::class, $casts['enum_show_contact']);
    }

    public function testEnumStatusAttributeReturnsEnumInstance(): void
    {
        $model = $this->makeModel();
        $model->enum_status = ArtistStatus::Published->value;

        $this->assertInstanceOf(ArtistStatus::class, $model->enum_status);
        $this->assertEquals(ArtistStatus::Published, $model->enum_status);
    }

    public function testEnumShowContactAttributeReturnsEnumInstance(): void
    {
        $model = $this->makeModel();
        $model->enum_show_contact = ArtistShowContact::SHOW->value;

        $this->assertInstanceOf(ArtistShowContact::class, $model->enum_show_contact);
        $this->assertEquals(ArtistShowContact::SHOW, $model->enum_show_contact);
    }

    public function testKeywordsRelation(): void
    {
        $relation = $this->makeModel()->keywords();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
        $this->assertEquals('keywords_artists', $relation->getTable());
        $this->assertEquals('artist_id', $relation->getForeignPivotKeyName());
        $this->assertEquals('keyword_id', $relation->getRelatedPivotKeyName());
    }

    public function testKeywordsArtistsRelation(): void
    {
        $relation = $this->makeModel()->keywordsArtists();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('artist_id', $relation->getForeignKeyName());
    }
}

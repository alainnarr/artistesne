<?php

namespace Tests\Unit\Database\Models;

use App\Database\Models\Artist;
use App\Database\Models\ArtistChangeRequest;
use App\Database\Models\Discipline;
use App\Database\Models\Registration;
use App\Database\Models\Repository;
use App\Enums\ArtistShowContact;
use App\Enums\ArtistStatus;
use App\Database\Models\User;
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
        return new Artist;
    }

    public function test_get_table_returns_table_name(): void
    {
        $model = $this->makeModel();

        $this->assertEquals('artists', $model->getTable());
    }

    public function test_get_fillable_returns_array(): void
    {
        $model = $this->makeModel();

        $this->assertEquals([
            'registration_id',
            'user_id',
            'slug',
            'artist_name',
            'email',
            'phone',
            'biography',
            'city',
            'discipline_main_id',
            'discipline_secondary',
            'activities',
            'secondary_activities',
            'keywords',
            'links',
            'collaborations',
            'enum_status',
            'enum_show_contact',
            'published_at',
            'last_confirmed_at',
            'reminder_sent_at',
            'confirmation_token',
        ], $model->getFillable());
    }

    public function test_get_rules_returns_empty_array(): void
    {
        $rules = Artist::getRules();

        $this->assertCount(13, $rules);
        $this->assertEquals('required|exists:registrations,id', $rules['registration_id']);
        $this->assertEquals('required|exists:newusers,id', $rules['user_id']);
        $this->assertEquals('required|string|max:255', $rules['artist_name']);
        $this->assertEquals('nullable|email|max:125', $rules['email']);
        $this->assertEquals('nullable|string|max:15', $rules['phone']);
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

    public function test_get_rules_returns_empty_array_when_filtering_fields(): void
    {
        $rules = Artist::getRules(['artist_name']);

        $this->assertCount(1, $rules);
        $this->assertArrayHasKey('artist_name', $rules);
        $this->assertEquals('required|string|max:255', $rules['artist_name']);
    }

    public function test_discipline_main_relation(): void
    {
        $relation = $this->makeModel()->activities();

        $relation = $model->disciplineMain();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Discipline::class, $relation->getRelated());
    }

    public function test_discipline_secondary_relation(): void
    {
        $relation = $this->makeModel()->disciplineSecondary();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Discipline::class, $relation->getRelated());
    }

    public function test_registration_relation(): void
    {
        $relation = $this->makeModel()->registration();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Registration::class, $relation->getRelated());
    }

    public function test_user_relation(): void
    {
        $relation = $this->makeModel()->user();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(User::class, $relation->getRelated());
    }

    public function test_rep_image_relation(): void
    {
        $model = $this->makeModel();

        $relation = $model->repImage();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Repository::class, $relation->getRelated());
    }

    public function test_repositories_relation(): void
    {
        $relation = $this->makeModel()->repositories();

        $this->assertInstanceOf(MorphMany::class, $relation);
        $this->assertInstanceOf(Repository::class, $relation->getRelated());
    }

    public function test_change_requests_relation(): void
    {
        $model = $this->makeModel();

        $relation = $model->changeRequests();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf(ArtistChangeRequest::class, $relation->getRelated());
    }

    public function test_casts_enum_status_to_artist_status(): void
    {
        $casts = $this->makeModel()->getCasts();

        $this->assertArrayHasKey('enum_status', $casts);
        $this->assertEquals(ArtistStatus::class, $casts['enum_status']);
    }

    public function test_casts_enum_show_contact_to_artist_show_contact(): void
    {
        $casts = $this->makeModel()->getCasts();

        $this->assertArrayHasKey('enum_show_contact', $casts);
        $this->assertEquals(ArtistShowContact::class, $casts['enum_show_contact']);
    }

    public function test_enum_status_attribute_returns_enum_instance(): void
    {
        $model = $this->makeModel();
        $model->enum_status = ArtistStatus::Published->value;

        $this->assertInstanceOf(ArtistStatus::class, $model->enum_status);
        $this->assertEquals(ArtistStatus::Published, $model->enum_status);
    }

    public function test_enum_show_contact_attribute_returns_enum_instance(): void
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

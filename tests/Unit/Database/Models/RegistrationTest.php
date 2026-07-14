<?php

namespace Tests\Unit\Database;

use App\Database\Models\Activity;
use App\Database\Models\Artist;
use App\Database\Models\Discipline;
use App\Database\Models\Link;
use App\Database\Models\Registration;
use App\Database\Models\Repository;
use App\Database\Models\User;
use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\Rules\Enum;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function makeModel(): Registration
    {
        return new Registration();
    }

    public function testGetTableReturnsTableName(): void
    {
        $this->assertEquals('registrations', $this->makeModel()->getTable());
    }

    public function testGetFillableReturnsArray(): void
    {
        $this->assertEquals([
            'real_name',
            'artist_name',
            'url',
            'birth_date',
            'email',
            'phone',
            'residence_location',
            'locality',
            'canton_link',
            'discipline_main',
            'discipline_secondary',
            'training',
            'paid_work',
            'recognition',
            'recent_achievements',
            'last_work',
            'enum_status',
            'reviewed_at',
            'reviewed_by',
            'review_notes',
        ], $this->makeModel()->getFillable());
    }

    public function testGetUpdatableReturnsArray(): void
    {
        $this->assertEquals([], $this->makeModel()->getUpdatable());
    }

    public function testCastsReturnsEnumStatus(): void
    {
        $casts = $this->makeModel()->getCasts();

        $this->assertArrayHasKey('enum_status', $casts);
        $this->assertEquals(RegistrationStatus::class, $casts['enum_status']);
    }

    public function testEnumStatusAttributeReturnsEnumInstance(): void
    {
        $model = $this->makeModel();
        $model->enum_status = RegistrationStatus::APPROVED->value;

        $this->assertInstanceOf(RegistrationStatus::class, $model->enum_status);
        $this->assertEquals(RegistrationStatus::APPROVED, $model->enum_status);
    }

    public function testGetRulesReturnsValidationRules(): void
    {
        $rules = Registration::getRules();

        $this->assertCount(20, $rules);

        $this->assertEquals('required|string|max:125', $rules['real_name']);
        $this->assertEquals('required|string|max:125', $rules['artist_name']);
        $this->assertEquals('required|string|max:255', $rules['url']);
        $this->assertEquals('required|date', $rules['birth_date']);
        $this->assertIsArray($rules['email']);
        $this->assertContains('required', $rules['email']);
        $this->assertContains('email', $rules['email']);
        $this->assertContains('max:125', $rules['email']);
        $this->assertEquals('required|string|max:15', $rules['phone']);
        $this->assertEquals('required|string|max:125', $rules['residence_location']);
        $this->assertEquals('nullable|string|max:125', $rules['locality']);
        $this->assertEquals('nullable|string', $rules['canton_link']);
        $this->assertEquals('required|integer|exists:disciplines,id', $rules['discipline_main']);
        $this->assertEquals('nullable|integer|exists:disciplines,id', $rules['discipline_secondary']);
        $this->assertEquals('nullable|string', $rules['training']);
        $this->assertEquals('nullable|string', $rules['paid_work']);
        $this->assertEquals('nullable|string', $rules['recognition']);
        $this->assertEquals('nullable|string', $rules['recent_achievements']);
        $this->assertEquals('nullable|string', $rules['last_work']);
        $this->assertIsArray($rules['enum_status']);
        $this->assertEquals('required', $rules['enum_status'][0]);
        $this->assertInstanceOf(Enum::class, $rules['enum_status'][1]);
        $this->assertEquals('nullable|date', $rules['reviewed_at']);
        $this->assertEquals('nullable|integer|exists:users,id', $rules['reviewed_by']);
        $this->assertEquals('nullable|string', $rules['review_notes']);
    }

    public function testGetRulesFiltersByFields(): void
    {
        $rules = Registration::getRules(['real_name', 'email']);

        $this->assertCount(2, $rules);
        $this->assertArrayHasKey('real_name', $rules);
        $this->assertArrayHasKey('email', $rules);
    }

    public function testGetRulesReturnsIntersectionOnlyForKnownFields(): void
    {
        $rules = Registration::getRules(['real_name', 'unknown_field']);

        $this->assertCount(1, $rules);
        $this->assertArrayHasKey('real_name', $rules);
    }

    public function testActivitiesRelation(): void
    {
        $relation = $this->makeModel()->activities();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
        $this->assertInstanceOf(Activity::class, $relation->getRelated());
        $this->assertEquals('activities_registrations', $relation->getTable());
        $this->assertEquals('registration_id', $relation->getForeignPivotKeyName());
        $this->assertEquals('activity_id', $relation->getRelatedPivotKeyName());
    }

    public function testActivitiesRegistrationsRelation(): void
    {
        $relation = $this->makeModel()->activitiesRegistrations();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('registration_id', $relation->getForeignKeyName());
    }

    public function testDisciplineMainRelation(): void
    {
        $relation = $this->makeModel()->disciplineMain();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Discipline::class, $relation->getRelated());
        $this->assertEquals('discipline_main', $relation->getForeignKeyName());
    }

    public function testDisciplineSecondaryRelation(): void
    {
        $relation = $this->makeModel()->disciplineSecondary();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Discipline::class, $relation->getRelated());
        $this->assertEquals('discipline_secondary', $relation->getForeignKeyName());
    }

    public function testArtistRelation(): void
    {
        $relation = $this->makeModel()->artist();

        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertInstanceOf(Artist::class, $relation->getRelated());
        $this->assertEquals('registration_id', $relation->getForeignKeyName());
    }

    public function testRepositoriesRelation(): void
    {
        $relation = $this->makeModel()->repositories();

        $this->assertInstanceOf(MorphMany::class, $relation);
        $this->assertInstanceOf(Repository::class, $relation->getRelated());
        $this->assertEquals('repositoryable_type', $relation->getMorphType());
    }

    public function testLinksRelation(): void
    {
        $relation = $this->makeModel()->links();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf(Link::class, $relation->getRelated());
        $this->assertEquals('registration_id', $relation->getForeignKeyName());
    }

    public function testReviewedByRelation(): void
    {
        $relation = $this->makeModel()->reviewedBy();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(User::class, $relation->getRelated());
        $this->assertEquals('reviewed_by', $relation->getForeignKeyName());
    }

    public function testNameAttributeReturnsArtistNameWhenPresent(): void
    {
        $model = $this->makeModel();

        $model->real_name = 'John Smith';
        $model->artist_name = 'Johnny';

        $this->assertEquals('Johnny', $model->name);
    }

    public function testNameAttributeReturnsRealNameWhenArtistNameIsEmpty(): void
    {
        $model = $this->makeModel();

        $model->real_name = 'John Smith';
        $model->artist_name = '';

        $this->assertEquals('John Smith', $model->name);
    }

    public function testCityAttributeReturnsLocalityWhenAvailable(): void
    {
        $model = $this->makeModel();

        $model->locality = 'Bern';
        $model->residence_location = 'Switzerland';

        $this->assertEquals('Bern', $model->city);
    }

    public function testCityAttributeReturnsResidenceLocationWhenLocalityIsEmpty(): void
    {
        $model = $this->makeModel();

        $model->locality = '';
        $model->residence_location = 'Switzerland';

        $this->assertEquals('Switzerland', $model->city);
    }
}

<?php

namespace Tests\Unit\Database\Models;

use App\Database\Models\Registration;
use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function makeModel(): Registration
    {
        return new Registration;
    }

    public function test_get_table_returns_table_name(): void
    {
        $model = $this->makeModel();

        $this->assertEquals('registrations', $model->getTable());
    }

    public function test_get_fillable_returns_array(): void
    {
        $model = $this->makeModel();

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
        ], $model->getFillable());
    }

    public function test_casts_returns_enum_status(): void
    {
        $model = $this->makeModel();

        $this->assertEquals(RegistrationStatus::class, $model->getCasts()['enum_status']);
    }

    public function test_get_rules_returns_all_rules_when_fields_empty(): void
    {
        $rules = Registration::getRules();

        $this->assertArrayHasKey('real_name', $rules);
        $this->assertArrayHasKey('artist_name', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('enum_status', $rules);
        $this->assertCount(20, $rules);
    }

    public function test_get_rules_filters_by_fields(): void
    {
        $rules = Registration::getRules(['real_name', 'email']);

        $this->assertEquals(['real_name', 'email'], array_keys($rules));
    }

    public function test_get_rules_email_contains_required_and_unique_rules(): void
    {
        $rules = Registration::getRules(['email']);

        $this->assertIsArray($rules['email']);
        $this->assertContains('required', $rules['email']);
        $this->assertContains('email', $rules['email']);
    }

    public function test_get_rules_returns_intersection_only_for_known_fields(): void
    {
        $rules = Registration::getRules(['real_name', 'unknown_field']);

        $this->assertEquals(['real_name'], array_keys($rules));
    }

    public function test_activities_relation(): void
    {
        $model = $this->makeModel();
        $relation = $model->activities();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
        $this->assertEquals('activities_registrations', $relation->getTable());
        $this->assertEquals('registration_id', $relation->getForeignPivotKeyName());
        $this->assertEquals('activity_id', $relation->getRelatedPivotKeyName());
    }

    public function test_discipline_main_relation(): void
    {
        $model = $this->makeModel();
        $relation = $model->disciplineMain();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('discipline_main', $relation->getForeignKeyName());
    }

    public function test_discipline_secondary_relation(): void
    {
        $model = $this->makeModel();
        $relation = $model->disciplineSecondary();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('discipline_secondary', $relation->getForeignKeyName());
    }

    public function test_repositories_relation(): void
    {
        $model = $this->makeModel();
        $relation = $model->repositories();

        $this->assertInstanceOf(MorphMany::class, $relation);
        $this->assertEquals('repositoryable_type', $relation->getMorphType());
    }
}

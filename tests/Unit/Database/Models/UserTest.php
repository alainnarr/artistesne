<?php

namespace Tests\Unit\Database\Models;

use App\Database\Models\Artist;
use App\Database\Models\ArtistChangeRequest;
use App\Database\Models\Registration;
use App\Database\Models\User;
use App\Enums\UserRole;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\Rules\Enum;
use Mockery;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private function makeModel(): User
    {
        return new User();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function testGetTableReturnsTableName(): void
    {
        $this->assertEquals('newusers', $this->makeModel()->getTable());
    }

    public function testGetFillableReturnsArray(): void
    {
        $this->assertEquals([
            'uuid',
            'email',
            'name',
            'enum_role',
            'adfs_id',
            'magic_link',
        ], $this->makeModel()->getFillable());
    }

    public function testGetUpdatableReturnsArray(): void
    {
        $this->assertEquals(['magic_link'], $this->makeModel()->getUpdatable());
    }

    public function testCastsReturnsEnumRole(): void
    {
        $casts = $this->makeModel()->getCasts();

        $this->assertArrayHasKey('enum_role', $casts);
        $this->assertEquals(UserRole::class, $casts['enum_role']);
    }

    public function testEnumRoleAttributeReturnsEnumInstance(): void
    {
        $user = $this->makeModel();
        $user->enum_role = UserRole::Admin->value;

        $this->assertInstanceOf(UserRole::class, $user->enum_role);
        $this->assertEquals(UserRole::Admin, $user->enum_role);
    }

    public function testGetRulesReturnsValidationRules(): void
    {
        $rules = User::getRules();

        $this->assertCount(6, $rules);
        $this->assertEquals('required|string|max:36|unique:newusers,uuid,,id', $rules['uuid']);
        $this->assertEquals('required|string|email|max:125|unique:newusers,email,,id', $rules['email']);
        $this->assertEquals('required|string|max:125', $rules['name']);
        $this->assertIsArray($rules['enum_role']);
        $this->assertEquals('required', $rules['enum_role'][0]);
        $this->assertInstanceOf(Enum::class, $rules['enum_role'][1]);
        $this->assertEquals('nullable|string|max:255|unique:newusers,adfs_id,,id', $rules['adfs_id']);
        $this->assertEquals('nullable|string|max:255', $rules['magic_link']);
    }

    public function testGetRulesWithRegisterIgnoresCurrentRecord(): void
    {
        $rules = User::getRules([], ['id' => 10]);

        $this->assertEquals('required|string|max:36|unique:newusers,uuid,10,id', $rules['uuid']);
        $this->assertEquals('required|string|email|max:125|unique:newusers,email,10,id', $rules['email']);
        $this->assertEquals('nullable|string|max:255|unique:newusers,adfs_id,10,id', $rules['adfs_id']);
    }

    public function testGetRulesFiltersByFields(): void
    {
        $rules = User::getRules(['email', 'enum_role']);

        $this->assertCount(2, $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('enum_role', $rules);
    }

    public function testGetRulesReturnsIntersectionOnlyForKnownFields(): void
    {
        $rules = User::getRules(['email', 'unknown']);

        $this->assertCount(1, $rules);
        $this->assertArrayHasKey('email', $rules);
    }

    public function testArtistRelation(): void
    {
        $relation = $this->makeModel()->artist();

        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertInstanceOf(Artist::class, $relation->getRelated());
        $this->assertEquals('user_id', $relation->getForeignKeyName());
    }

    public function testReviewedRegistrationsRelation(): void
    {
        $relation = $this->makeModel()->reviewedRegistrations();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf(Registration::class, $relation->getRelated());
        $this->assertEquals('reviewed_by', $relation->getForeignKeyName());
    }

    public function testReviewedArtistsChangeRequestsRelation(): void
    {
        $relation = $this->makeModel()->reviewedArtistsChangeRequests();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf(ArtistChangeRequest::class, $relation->getRelated());
        $this->assertEquals('reviewed_by', $relation->getForeignKeyName());
    }

    public function testIsAdminReturnsTrueForAdmin(): void
    {
        $user = $this->makeModel();
        $user->enum_role = UserRole::Admin;

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isArtist());
    }

    public function testIsArtistReturnsTrueForArtist(): void
    {
        $user = $this->makeModel();
        $user->enum_role = UserRole::Artist;

        $this->assertTrue($user->isArtist());
        $this->assertFalse($user->isAdmin());
    }

    public function testCanAccessAdminPanel(): void
    {
        $panel = Mockery::mock(Panel::class);
        $panel->shouldReceive('getId')
            ->once()
            ->andReturn('admin');
        $user = $this->makeModel();
        $user->enum_role = UserRole::Admin;

        $this->assertTrue($user->canAccessPanel($panel)
        );
    }

    public function testCannotAccessNonAdminPanel(): void
    {
        $panel = Mockery::mock(Panel::class);
        $panel->shouldReceive('getId')
            ->once()
            ->andReturn('artist');
        $user = $this->makeModel();
        $user->enum_role = UserRole::Admin;

        $this->assertFalse($user->canAccessPanel($panel));
    }

    public function testArtistCannotAccessAdminPanel(): void
    {
        $panel = Mockery::mock(Panel::class);
        $panel->shouldReceive('getId')
            ->once()
            ->andReturn('admin');
        $user = $this->makeModel();
        $user->enum_role = UserRole::Artist;

        $this->assertFalse($user->canAccessPanel($panel));
    }

    public function testInitialsReturnsFirstLettersOfFirstTwoNames(): void
    {
        $user = $this->makeModel();
        $user->name = 'John Doe';

        $this->assertEquals('JD', $user->initials());
    }

    public function testInitialsReturnsSingleLetterForSingleName(): void
    {
        $user = $this->makeModel();
        $user->name = 'Madonna';

        $this->assertEquals('M', $user->initials());
    }

    public function testInitialsIgnoresNamesAfterSecondWord(): void
    {
        $user = $this->makeModel();
        $user->name = 'Jean Claude Van Damme';

        $this->assertEquals('JC', $user->initials());
    }
}

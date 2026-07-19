<?php

namespace Tests\Unit\Services;

use App\Database\Models\User;
use App\Enums\UserRole;
use App\Services\UsersService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UsersServiceTest extends TestCase
{
    use DatabaseTransactions;

    private UsersService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new UsersService();
    }

    public function testCreateCreatesArtistUserByDefault(): void
    {
        $user = $this->service->create('artist@test.com', 'Artist Test');

        $this->assertDatabaseHas('newusers', [
            'id' => $user->id,
            'email' => 'artist@test.com',
            'name' => 'Artist Test',
            'enum_role' => UserRole::ARTIST->value,
        ]);
        $this->assertTrue(Str::isUuid($user->uuid));
    }

    public function testCreateCreatesUserWithAllParameters(): void
    {
        $user = $this->service->create(
            email: 'admin@test.com',
            name: 'Administrator',
            role: UserRole::ADMIN,
            adfsId: 'ADFS-123',
            magicLink: 'magic-token'
        );

        $this->assertDatabaseHas('newusers', [
            'id' => $user->id,
            'email' => 'admin@test.com',
            'name' => 'Administrator',
            'enum_role' => UserRole::ADMIN->value,
            'adfs_id' => 'ADFS-123',
            'magic_link' => 'magic-token',
        ]);
    }

    public function testCreateGeneratesUuid(): void
    {
        $user = $this->service->create('uuid@test.com', 'UUID Test');

        $this->assertNotNull($user->uuid);
        $this->assertTrue(Str::isUuid($user->uuid));
    }

    public function testCreateThrowsValidationExceptionForInvalidEmail(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->create('invalid-email', 'Test');
    }

    public function testCreateThrowsValidationExceptionForDuplicateEmail(): void
    {
        $this->service->create('duplicate@test.com', 'First');

        $this->expectException(ValidationException::class);
        $this->service->create('duplicate@test.com', 'Second');
    }
}

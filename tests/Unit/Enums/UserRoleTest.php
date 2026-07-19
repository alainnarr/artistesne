<?php

namespace Tests\Unit\Enumerations;

use App\Enums\UserRole;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testLabelReturnsCorrectLabel(): void
    {
        $this->assertEquals('Administrateur', UserRole::ADMIN->label());
        $this->assertEquals('Artiste', UserRole::ARTIST->label());
    }

    public function testEnumHasCorrectValues(): void
    {
        $this->assertEquals('admin', UserRole::ADMIN->value);
        $this->assertEquals('artist', UserRole::ARTIST->value);
    }

    public function testCanCreateEnumFromValue(): void
    {
        $this->assertSame(UserRole::ADMIN, UserRole::from('admin'));
        $this->assertSame(UserRole::ARTIST, UserRole::from('artist'));
    }

    public function testTryFromReturnsNullForInvalidValue(): void
    {
        $this->assertNull(UserRole::tryFrom('invalid'));
    }
}

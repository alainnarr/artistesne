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
        $this->assertEquals('Administrateur', UserRole::Admin->label());
        $this->assertEquals('Artiste', UserRole::Artist->label());
    }

    public function testEnumHasCorrectValues(): void
    {
        $this->assertEquals('admin', UserRole::Admin->value);
        $this->assertEquals('artist', UserRole::Artist->value);
    }

    public function testCanCreateEnumFromValue(): void
    {
        $this->assertSame(UserRole::Admin, UserRole::from('admin'));
        $this->assertSame(UserRole::Artist, UserRole::from('artist'));
    }

    public function testTryFromReturnsNullForInvalidValue(): void
    {
        $this->assertNull(UserRole::tryFrom('invalid'));
    }
}

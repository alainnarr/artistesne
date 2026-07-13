<?php

namespace Tests\Unit\Enumerations;

use App\Enums\RegistrationStatus;
use Tests\TestCase;

class RegistrationStatusTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testLabelReturnsCorrectLabel(): void
    {
        $this->assertEquals('Ouvert', RegistrationStatus::OPEN->label());
        $this->assertEquals('Approuvé', RegistrationStatus::APPROVED->label());
        $this->assertEquals('Rejeté', RegistrationStatus::REJECTED->label());
        $this->assertEquals('En attente', RegistrationStatus::PENDING->label());
    }

    public function testEnumHasCorrectValues(): void
    {
        $this->assertEquals('open', RegistrationStatus::OPEN->value);
        $this->assertEquals('approved', RegistrationStatus::APPROVED->value);
        $this->assertEquals('rejected', RegistrationStatus::REJECTED->value);
        $this->assertEquals('pending', RegistrationStatus::PENDING->value);
    }

    public function testCanCreateEnumFromValue(): void
    {
        $this->assertSame(RegistrationStatus::OPEN, RegistrationStatus::from('open'));
        $this->assertSame(RegistrationStatus::APPROVED, RegistrationStatus::from('approved'));
        $this->assertSame(RegistrationStatus::REJECTED, RegistrationStatus::from('rejected'));
        $this->assertSame(RegistrationStatus::PENDING, RegistrationStatus::from('pending'));
    }

    public function testTryFromReturnsNullForInvalidValue(): void
    {
        $this->assertNull(RegistrationStatus::tryFrom('invalid'));
    }
}

<?php

namespace Tests\Unit\Enums;

use App\Enums\RegistrationStatus;
use Tests\TestCase;

class RegistrationStatusTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_label_returns_correct_label(): void
    {
        $this->assertEquals('Ouvert', RegistrationStatus::OPEN->label());
        $this->assertEquals('Approuvé', RegistrationStatus::APPROVED->label());
        $this->assertEquals('Rejeté', RegistrationStatus::REJECTED->label());
        $this->assertEquals('En attente', RegistrationStatus::PENDING->label());
    }

    public function test_enum_has_correct_values(): void
    {
        $this->assertEquals('open', RegistrationStatus::OPEN->value);
        $this->assertEquals('approved', RegistrationStatus::APPROVED->value);
        $this->assertEquals('rejected', RegistrationStatus::REJECTED->value);
        $this->assertEquals('pending', RegistrationStatus::PENDING->value);
    }

    public function test_can_create_enum_from_value(): void
    {
        $this->assertSame(RegistrationStatus::OPEN, RegistrationStatus::from('open'));
        $this->assertSame(RegistrationStatus::APPROVED, RegistrationStatus::from('approved'));
        $this->assertSame(RegistrationStatus::REJECTED, RegistrationStatus::from('rejected'));
        $this->assertSame(RegistrationStatus::PENDING, RegistrationStatus::from('pending'));
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(RegistrationStatus::tryFrom('invalid'));
    }
}

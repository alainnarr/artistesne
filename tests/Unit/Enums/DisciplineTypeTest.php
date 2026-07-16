<?php

namespace Tests\Unit\Enums;

use App\Enums\DisciplineType;
use Tests\TestCase;

class DisciplineTypeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_label_returns_correct_label(): void
    {
        $this->assertEquals('principal', DisciplineType::MAIN->label());
        $this->assertEquals('secondaire', DisciplineType::SECONDARY->label());
    }

    public function test_enum_has_correct_values(): void
    {
        $this->assertEquals('main', DisciplineType::MAIN->value);
        $this->assertEquals('secondary', DisciplineType::SECONDARY->value);
    }

    public function test_can_create_enum_from_value(): void
    {
        $this->assertSame(DisciplineType::MAIN, DisciplineType::from('main'));
        $this->assertSame(DisciplineType::SECONDARY, DisciplineType::from('secondary'));
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(DisciplineType::tryFrom('invalid'));
    }
}

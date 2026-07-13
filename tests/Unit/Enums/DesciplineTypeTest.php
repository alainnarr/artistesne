<?php

namespace Tests\Unit\Enumerations;

use App\Enums\DisciplineType;
use Tests\TestCase;

class DisciplineTypeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testLabelReturnsCorrectLabel(): void
    {
        $this->assertEquals('principal', DisciplineType::MAIN->label());
        $this->assertEquals('secondaire', DisciplineType::SECONDARY->label());
    }

    public function testEnumHasCorrectValues(): void
    {
        $this->assertEquals('main', DisciplineType::MAIN->value);
        $this->assertEquals('secondary', DisciplineType::SECONDARY->value);
    }

    public function testCanCreateEnumFromValue(): void
    {
        $this->assertSame(DisciplineType::MAIN, DisciplineType::from('main'));
        $this->assertSame(DisciplineType::SECONDARY, DisciplineType::from('secondary'));
    }

    public function testTryFromReturnsNullForInvalidValue(): void
    {
        $this->assertNull(DisciplineType::tryFrom('invalid'));
    }
}

<?php

namespace Tests\Unit\Enums;

use App\Enums\LinkType;
use PHPUnit\Framework\TestCase;

class LinkTypeTest extends TestCase
{
    public function testEnumContainsExpectedValues(): void
    {
        $this->assertEquals('website', LinkType::WEBSITE->value);
        $this->assertEquals('instagram', LinkType::INSTAGRAM->value);
        $this->assertEquals('youtube', LinkType::YOUTUBE->value);
        $this->assertEquals('other', LinkType::OTHER->value);
    }

    public function testLabelReturnsCorrectValue(): void
    {
        $this->assertEquals('Site personnel', LinkType::WEBSITE->label());
        $this->assertEquals('Instagram', LinkType::INSTAGRAM->label());
        $this->assertEquals('YouTube', LinkType::YOUTUBE->label());
        $this->assertEquals('Autre', LinkType::OTHER->label());
    }

    public function testAllCasesHaveLabels(): void
    {
        foreach (LinkType::cases() as $type) {
            $this->assertNotEmpty($type->label());
        }
    }
}

<?php

namespace Tests\Unit\Enums;

use App\Enums\ArtistShowContact;
use PHPUnit\Framework\TestCase;

class ArtistShowContactTest extends TestCase
{
    public function testLabelReturnsHide(): void
    {
        $this->assertEquals('Hide', ArtistShowContact::HIDE->label());
    }

    public function testLabelReturnsShow(): void
    {
        $this->assertEquals('Show', ArtistShowContact::SHOW->label());
    }

    public function testToBoolReturnsFalseForHide(): void
    {
        $this->assertFalse(ArtistShowContact::HIDE->toBool());
    }

    public function testToBoolReturnsTrueForShow(): void
    {
        $this->assertTrue(ArtistShowContact::SHOW->toBool());
    }

    public function testFromBoolReturnsHide(): void
    {
        $this->assertEquals(
            ArtistShowContact::HIDE,
            ArtistShowContact::fromBool(false)
        );
    }

    public function testFromBoolReturnsShow(): void
    {
        $this->assertEquals(
            ArtistShowContact::SHOW,
            ArtistShowContact::fromBool(true)
        );
    }
}

<?php

namespace Tests\Unit\Enums;

use App\Enums\ArtistShowContact;
use PHPUnit\Framework\TestCase;

class ArtistShowContactTest extends TestCase
{
    public function test_label_returns_hide(): void
    {
        $this->assertEquals('Hide', ArtistShowContact::HIDE->label());
    }

    public function test_label_returns_show(): void
    {
        $this->assertEquals('Show', ArtistShowContact::SHOW->label());
    }

    public function test_to_bool_returns_false_for_hide(): void
    {
        $this->assertFalse(ArtistShowContact::HIDE->toBool());
    }

    public function test_to_bool_returns_true_for_show(): void
    {
        $this->assertTrue(ArtistShowContact::SHOW->toBool());
    }

    public function test_from_bool_returns_hide(): void
    {
        $this->assertEquals(
            ArtistShowContact::HIDE,
            ArtistShowContact::fromBool(false)
        );
    }

    public function test_from_bool_returns_show(): void
    {
        $this->assertEquals(
            ArtistShowContact::SHOW,
            ArtistShowContact::fromBool(true)
        );
    }
}

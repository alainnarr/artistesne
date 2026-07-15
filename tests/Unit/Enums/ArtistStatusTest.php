<?php

namespace Tests\Unit\Enums;

use App\Enums\ArtistStatus;
use PHPUnit\Framework\TestCase;

class ArtistStatusTest extends TestCase
{
    public function test_draft_label(): void
    {
        $this->assertEquals('Brouillon', ArtistStatus::Draft->label());
    }

    public function test_published_label(): void
    {
        $this->assertEquals('Publié', ArtistStatus::Published->label());
    }

    public function test_draft_color(): void
    {
        $this->assertEquals('gray', ArtistStatus::Draft->color());
    }

    public function test_published_color(): void
    {
        $this->assertEquals('success', ArtistStatus::Published->color());
    }

    public function test_draft_value(): void
    {
        $this->assertEquals('draft', ArtistStatus::Draft->value);
    }

    public function test_published_value(): void
    {
        $this->assertEquals('published', ArtistStatus::Published->value);
    }

    public function test_from_draft_value(): void
    {
        $this->assertEquals(
            ArtistStatus::Draft,
            ArtistStatus::from('draft')
        );
    }

    public function test_from_published_value(): void
    {
        $this->assertEquals(
            ArtistStatus::Published,
            ArtistStatus::from('published')
        );
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(ArtistStatus::tryFrom('invalid'));
    }
}

<?php

namespace Tests\Unit\Enums;

use App\Enums\ArtistStatus;
use PHPUnit\Framework\TestCase;

class ArtistStatusTest extends TestCase
{
    public function test_draft_label(): void
    {
        $this->assertEquals('Brouillon', ArtistStatus::DRAFT->label());
    }

    public function test_published_label(): void
    {
        $this->assertEquals('Publié', ArtistStatus::PUBLISHED->label());
    }

    public function test_draft_color(): void
    {
        $this->assertEquals('gray', ArtistStatus::DRAFT->color());
    }

    public function test_published_color(): void
    {
        $this->assertEquals('success', ArtistStatus::PUBLISHED->color());
    }

    public function test_draft_value(): void
    {
        $this->assertEquals('draft', ArtistStatus::DRAFT->value);
    }

    public function test_published_value(): void
    {
        $this->assertEquals('published', ArtistStatus::PUBLISHED->value);
    }

    public function test_from_draft_value(): void
    {
        $this->assertEquals(
            ArtistStatus::DRAFT,
            ArtistStatus::from('draft')
        );
    }

    public function test_from_published_value(): void
    {
        $this->assertEquals(
            ArtistStatus::PUBLISHED,
            ArtistStatus::from('published')
        );
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(ArtistStatus::tryFrom('invalid'));
    }
}

<?php

namespace Tests\Unit\Enums;

use App\Enums\ArtistStatus;
use PHPUnit\Framework\TestCase;

class ArtistStatusTest extends TestCase
{
    public function testDraftLabel(): void
    {
        $this->assertEquals('Brouillon', ArtistStatus::Draft->label());
    }

    public function testPublishedLabel(): void
    {
        $this->assertEquals('Publié', ArtistStatus::Published->label());
    }

    public function testDraftColor(): void
    {
        $this->assertEquals('gray', ArtistStatus::Draft->color());
    }

    public function testPublishedColor(): void
    {
        $this->assertEquals('success', ArtistStatus::Published->color());
    }

    public function testDraftValue(): void
    {
        $this->assertEquals('draft', ArtistStatus::Draft->value);
    }

    public function testPublishedValue(): void
    {
        $this->assertEquals('published', ArtistStatus::Published->value);
    }

    public function testFromDraftValue(): void
    {
        $this->assertEquals(
            ArtistStatus::Draft,
            ArtistStatus::from('draft')
        );
    }

    public function testFromPublishedValue(): void
    {
        $this->assertEquals(
            ArtistStatus::Published,
            ArtistStatus::from('published')
        );
    }

    public function testTryFromReturnsNullForInvalidValue(): void
    {
        $this->assertNull(ArtistStatus::tryFrom('invalid'));
    }
}

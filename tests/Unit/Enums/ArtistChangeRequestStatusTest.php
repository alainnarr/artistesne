<?php

namespace Tests\Unit\Enums;

use App\Enums\ArtistChangeRequestStatus;
use Tests\TestCase;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

class ArtistChangeRequestStatusTest extends TestCase
{
    public function testEnumCasesHaveExpectedValues(): void
    {
        $this->assertEquals('pending', ArtistChangeRequestStatus::PENDING->value);
        $this->assertEquals('approved', ArtistChangeRequestStatus::APPROVED->value);
        $this->assertEquals('rejected', ArtistChangeRequestStatus::REJECTED->value);
        $this->assertEquals('changes_requested', ArtistChangeRequestStatus::CHANGES_REQUESTED->value);
    }

    public function testLabelsAreReturnedCorrectly(): void
    {
        $this->assertEquals('En attente', ArtistChangeRequestStatus::PENDING->label());
        $this->assertEquals('Approuvée', ArtistChangeRequestStatus::APPROVED->label());
        $this->assertEquals('Refusée', ArtistChangeRequestStatus::REJECTED->label());
        $this->assertEquals('Modifications demandées', ArtistChangeRequestStatus::CHANGES_REQUESTED->label());
    }

    public function testColorsAreReturnedCorrectly(): void
    {
        $this->assertEquals('warning', ArtistChangeRequestStatus::PENDING->color());
        $this->assertEquals('success', ArtistChangeRequestStatus::APPROVED->color());
        $this->assertEquals('danger', ArtistChangeRequestStatus::REJECTED->color());
        $this->assertEquals('info', ArtistChangeRequestStatus::CHANGES_REQUESTED->color());
    }

    public function testIsPendingReturnsTrueOnlyForPendingStatus(): void
    {
        $this->assertTrue(ArtistChangeRequestStatus::PENDING->isPending());
        $this->assertFalse(ArtistChangeRequestStatus::APPROVED->isPending());
        $this->assertFalse(ArtistChangeRequestStatus::REJECTED->isPending());
        $this->assertFalse(ArtistChangeRequestStatus::CHANGES_REQUESTED->isPending());
    }

    public function testEnumImplementsFilamentContracts(): void
    {
        $reflection = new \ReflectionClass(ArtistChangeRequestStatus::class);
        $interfaces = $reflection->getInterfaceNames();

        $this->assertContains(HasColor::class, $interfaces);
        $this->assertContains(HasLabel::class, $interfaces);
    }
}

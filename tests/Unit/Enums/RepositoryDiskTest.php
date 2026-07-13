<?php

namespace Tests\Unit\Enumerations;

use App\Enums\RepositoryDisk;
use Tests\TestCase;

class RepositoryDiskTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testLabelReturnsCorrectLabel(): void
    {
        $this->assertEquals('Public', RepositoryDisk::PUBLIC->label());
        $this->assertEquals('Private', RepositoryDisk::PRIVATE->label());
    }

    public function testEnumHasCorrectValues(): void
    {
        $this->assertEquals('public', RepositoryDisk::PUBLIC->value);
        $this->assertEquals('private', RepositoryDisk::PRIVATE->value);
    }

    public function testCanCreateEnumFromValue(): void
    {
        $this->assertSame(RepositoryDisk::PUBLIC, RepositoryDisk::from('public'));
        $this->assertSame(RepositoryDisk::PRIVATE, RepositoryDisk::from('private'));
    }

    public function testTryFromReturnsNullForInvalidValue(): void
    {
        $this->assertNull(RepositoryDisk::tryFrom('invalid'));
    }
}

<?php

namespace Tests\Unit\Enums;

use App\Enums\RepositoryDisk;
use Tests\TestCase;

class RepositoryDiskTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_label_returns_correct_label(): void
    {
        $this->assertEquals('Public', RepositoryDisk::PUBLIC->label());
        $this->assertEquals('Private', RepositoryDisk::PRIVATE->label());
    }

    public function test_enum_has_correct_values(): void
    {
        $this->assertEquals('public', RepositoryDisk::PUBLIC->value);
        $this->assertEquals('private', RepositoryDisk::PRIVATE->value);
    }

    public function test_can_create_enum_from_value(): void
    {
        $this->assertSame(RepositoryDisk::PUBLIC, RepositoryDisk::from('public'));
        $this->assertSame(RepositoryDisk::PRIVATE, RepositoryDisk::from('private'));
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(RepositoryDisk::tryFrom('invalid'));
    }
}

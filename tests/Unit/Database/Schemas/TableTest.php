<?php

namespace Test\Unit\Database\Schemas;

use App\Database\Schemas\Table;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class TableTest extends TestCase
{
    use RefreshDatabase;

    private string $table = 'example';

    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropIfExists('test_repositories');
        Schema::dropIfExists($this->table);
    }

    public function testCreateTableWithDefaultColumns(): void
    {
        Table::make($this->table, function (Blueprint $table) {
            $table->string('name');
        });

        $this->assertTrue(Schema::hasTable($this->table));

        $columns = collect(DB::select("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
        ", [env('DB_DATABASE'), $this->table]))->pluck('COLUMN_NAME');

        foreach (
            [
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
            'audit_action',
            'audit_url',
            'audit_ip'
            ] as $col
        ) {
            $this->assertTrue($columns->contains($col), "Column $col should exist in table");
        }
    }

    public function testEnumerationIntBooleanString(): void
    {
        Table::make($this->table, function ($table) {
            $table->enumeration('int_col', 'int');
            $table->enumeration('bool_col', 'boolean');
            $table->enumeration('str_col', 'string');
        });

        $columns = collect(DB::select("
            SELECT COLUMN_NAME, COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
        ", [env('DB_DATABASE'), $this->table]))->pluck('COLUMN_TYPE', 'COLUMN_NAME');

        $this->assertStringContainsString('int', $columns['int_col']);
        $this->assertStringContainsString('tinyint', $columns['bool_col']);
        $this->assertStringContainsString('varchar', $columns['str_col']);
    }

    public function testEnumerationThrowsExceptionOnInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Type: float not accepted for enumeration');

        Table::make($this->table, function ($table) {
            $table->enumeration('bad_col', 'float');
        });
    }

    public function testForeignKeyCreatesColumnWithConstraints(): void
    {
        Table::make($this->table, function ($table) {
            $table->foreignKey('user_id', 'users', 'id', 'bigInteger');
        });

        $columns = collect(DB::select("
            SELECT COLUMN_NAME, COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
        ", [env('DB_DATABASE'), $this->table]))->pluck('COLUMN_TYPE', 'COLUMN_NAME');

        $this->assertStringContainsString('bigint', $columns['user_id']);
    }

    public function testForeignKeyThrowsExceptionOnInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Type: float not accepted for foreign key');

        Table::make($this->table, function ($table) {
            $table->foreignKey('bad_fk', 'some_table', 'id', 'float');
        });
    }

    public function testRepositoryMethodSingleAndMultiple(): void
    {
        Table::make('test_repositories', function ($table) {
            $table->bigIncrements('id', false);
            $table->string('code', 255);
        });

        Table::make($this->table, function ($table) {
            $table->repository('single_repo', false);
            $table->repository('multi_repo', true);
        });

        $columns = collect(DB::select("
            SELECT COLUMN_NAME, COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
              AND COLUMN_NAME IN ('single_repo', 'multi_repo')
        ", [env('DB_DATABASE'), $this->table]))->pluck('COLUMN_TYPE', 'COLUMN_NAME');

        $this->assertStringContainsString('bigint', $columns['single_repo']);
        $this->assertStringContainsString('varchar', $columns['multi_repo']);
    }

    public function testDefaultColumnsAddsDeletedByIfDeletedAtExists(): void
    {
        Table::make($this->table, function ($table) {
            $table->softDeletes();
        });

        $columns = collect(DB::select("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
        ", [env('DB_DATABASE'), $this->table]))->pluck('COLUMN_NAME');

        $this->assertTrue($columns->contains('deleted_by'));
    }

    public function testForeignKeyAllTypes(): void
    {
        Table::make('references', function ($table) {
            $table->bigIncrements('col_integer');
            $table->uuid('col_uuid')->unique();
            $table->string('col_string', 255)->unique();
        });

        Table::make($this->table, function ($table) {
            $table->foreignKey('col_integer', 'references', 'col_integer', 'bigInteger');
            $table->foreignKey('col_uuid', 'references', 'col_uuid', 'uuid');
            $table->foreignKey('col_string', 'references', 'col_string', 'string');
        });

        $columns = collect(DB::select("
            SELECT COLUMN_NAME, COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
        ", [env('DB_DATABASE'), $this->table]))->pluck('COLUMN_TYPE', 'COLUMN_NAME');

        $this->assertStringContainsString('bigint', $columns['col_integer']);
        $this->assertStringContainsString('uuid', $columns['col_uuid']);
        $this->assertStringContainsString('varchar', $columns['col_string']);
    }
}

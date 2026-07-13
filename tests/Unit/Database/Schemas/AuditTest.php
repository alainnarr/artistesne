<?php

namespace Test\Unit\Database\Schemas;

use App\Database\Schemas\Audit;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuditTest extends TestCase
{
    use RefreshDatabase;

    private string $table = 'example';
    private string $auditTable = '_example';

    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropIfExists($this->auditTable);
    }

    public function testCreatesAuditTableWithPrefix()
    {
        Audit::make($this->table, function (Blueprint $table) {
            $table->string('name');
        });
        $this->assertTrue(Schema::hasTable($this->auditTable));
    }

    public function testAllColumnsBecomeNullable(): void
    {
        Audit::make($this->table, function (Blueprint $table) {
            $table->string('name');
            $table->integer('age');
        });

        $columns = DB::select("
            SELECT COLUMN_NAME, IS_NULLABLE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
        ", [env('DB_DATABASE'), $this->auditTable]);

        foreach ($columns as $col) {
            if ($col->COLUMN_NAME === '_id') {
                $this->assertEquals('NO', $col->IS_NULLABLE, "Column {$col->COLUMN_NAME} should be nullable");
            } else {
                $this->assertEquals('YES', $col->IS_NULLABLE, "Column {$col->COLUMN_NAME} should be nullable");
            }
        }
    }

    public function testOnlyInternalIdIsAutoincrement()
    {
        Audit::make($this->table, function (Blueprint $table) {
            $table->bigIncrements('id');
        });

        $columns = DB::select("
            SELECT COLUMN_NAME, EXTRA
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
        ", [env('DB_DATABASE'), $this->auditTable]);

        $extras = collect($columns)->pluck('EXTRA', 'COLUMN_NAME');

        $this->assertEquals('auto_increment', $extras['_id'] ?? '', '_id should be autoincrement');
        $this->assertNotEquals('auto_increment', $extras['id'] ?? '', 'id should NOT be autoincrement');
    }

    public function testUniqueConstraintsAreRemoved(): void
    {
        Audit::make($this->table, function (Blueprint $table) {
            $table->string('code')->unique();
        });

        $indexes = DB::select("
        SELECT INDEX_NAME, NON_UNIQUE
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
          AND INDEX_NAME != 'PRIMARY'
    ", [env('DB_DATABASE'), $this->auditTable]);

        if (empty($indexes)) {
            $this->assertEmpty($indexes, 'No indexes found to check.');
        } else {
            foreach ($indexes as $index) {
                $this->assertEquals(1, $index->NON_UNIQUE, "Index {$index->INDEX_NAME} should not be unique");
            }
        }
    }

    public function testNoForeignAreCreated(): void
    {
        Audit::make($this->table, function (Blueprint $table) {
            $table->foreignId('user_id');
            $table->foreignUuid('uuid_fk');
            $table->foreign('whatever');
        });

        $fks = DB::select("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [env('DB_DATABASE'), $this->auditTable]);

        $this->assertEmpty($fks, "Audit table should have no foreign keys");
    }

    public function testNoForeignKeysAreCreated(): void
    {
        Audit::make($this->table, function (Blueprint $table) {
            $table->foreignKey('int_column', 'fk_table', 'fk_int_column', 'integer');
            $table->foreignKey('uuid_column', 'fk_table', 'fk_int_column', 'uuid');
            $table->foreignKey('string_column', 'fk_table', 'fk_int_column', 'string');
        });

        $fks = DB::select("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [env('DB_DATABASE'), $this->auditTable]);

        $this->assertEmpty($fks, "Audit table should have no foreign keys");
    }

    public function testForeignIdBecomesUnsignedBigintWithoutConstraint(): void
    {
        Audit::make($this->table, function (Blueprint $table) {
            $table->foreignId('user_id');
        });

        $col = DB::selectOne("
            SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_KEY
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
        ", [env('DB_DATABASE'), $this->auditTable, 'user_id']);

        $this->assertStringContainsString('bigint', $col->COLUMN_TYPE);
        $this->assertStringContainsString('unsigned', $col->COLUMN_TYPE);
    }

    public function testForeignUuidBecomesUuidColumn(): void
    {
        Audit::make($this->table, function (Blueprint $table) {
            $table->foreignUuid('fk_uuid');
        });

        $col = DB::selectOne("
            SELECT COLUMN_NAME, COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
        ", [env('DB_DATABASE'), $this->auditTable, 'fk_uuid']);

        $this->assertEquals('uuid', $col->COLUMN_TYPE);
    }

    public function testForeignKeyMethodCreatesSimpleColumns(): void
    {
        Audit::make($this->table, function (Blueprint $table) {
            $table->foreignKey('repo', 'repositories', 'code', 'string', 100);
        });

        $col = DB::selectOne("
            SELECT COLUMN_NAME, COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
        ", [env('DB_DATABASE'), $this->auditTable, 'repo']);

        $this->assertStringContainsString('varchar(100)', $col->COLUMN_TYPE);
    }

    public function testRepositoryMethodCreatesSimpleColumnsWithoutFk(): void
    {
        Audit::make($this->table, function (Blueprint $table) {
            $table->repository('repo_id', false);
            $table->repository('repo_code', true);
        });

        $repoId = DB::selectOne("
            SELECT COLUMN_NAME, COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
        ", [env('DB_DATABASE'), $this->auditTable, 'repo_id']);
        $repoCode = DB::selectOne("
            SELECT COLUMN_NAME, COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
        ", [env('DB_DATABASE'), $this->auditTable, 'repo_code']);

        $this->assertStringContainsString('bigint', $repoId->COLUMN_TYPE);
        $this->assertStringContainsString('varchar', $repoCode->COLUMN_TYPE);

        $fks = DB::select("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [env('DB_DATABASE'), $this->auditTable]);

        $this->assertEmpty($fks);
    }

    public function testEnumerationIntBooleanString(): void
    {
        Audit::make($this->table, function ($table) {
            $table->enumeration('int_col', 'integer');
            $table->enumeration('bool_col', 'boolean');
            $table->enumeration('str_col', 'string');
        });

        $columns = DB::select("
            SELECT COLUMN_NAME, COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
        ", [env('DB_DATABASE'), $this->auditTable]);

        $types = collect($columns)->pluck('COLUMN_TYPE', 'COLUMN_NAME');

        $this->assertStringContainsString('int', $types['int_col']);
        $this->assertStringContainsString('tinyint', $types['bool_col']);
        $this->assertStringContainsString('varchar', $types['str_col']);
    }

    public function testEnumerationThrowsExceptionOnInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Type: float not accepted for enumeration');

        Audit::make($this->table, function ($table) {
            $table->enumeration('bad_col', 'float');
        });
    }

    public function testForeignKeyThrowsExceptionOnInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Type: text not accepted for foreign key');

        Audit::make($this->table, function ($table) {
            $table->foreignKey('bad_fk', 'some_table', 'id', 'text');
        });
    }

    public function testForeignUlidCreatesColumn(): void
    {
        Audit::make($this->table, function ($table) {
            $table->foreignUlid('ulid_col');
        });

        $col = DB::selectOne("
        SELECT COLUMN_NAME, COLUMN_TYPE
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
    ", [env('DB_DATABASE'), $this->auditTable, 'ulid_col']);

        $this->assertStringContainsString('char', $col->COLUMN_TYPE);
    }

    public function testIdCreatesUnsignedBigint(): void
    {
        Audit::make($this->table, function ($table) {
            $table->id('custom_id');
        });

        $col = DB::selectOne("
        SELECT COLUMN_NAME, COLUMN_TYPE
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
    ", [env('DB_DATABASE'), $this->auditTable, 'custom_id']);

        $this->assertStringContainsString('bigint', $col->COLUMN_TYPE);
        $this->assertStringContainsString('unsigned', $col->COLUMN_TYPE);
    }

    public function testForeignIdForReturnsNull(): void
    {
        Audit::make($this->table, function ($table) {
            $result = $table->foreignIdFor(\stdClass::class, 'user_uuid');
            $this->assertNull($result);
        });
    }

    public function testRepositoryMultipleTrueAndFalse(): void
    {
        Audit::make($this->table, function ($table) {
            $table->repository('single_repo', false);
            $table->repository('multi_repo', true);
        });

        $cols = DB::select("
        SELECT COLUMN_NAME, COLUMN_TYPE
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
          AND COLUMN_NAME IN ('single_repo','multi_repo')
    ", [env('DB_DATABASE'), $this->auditTable]);

        $types = collect($cols)->pluck('COLUMN_TYPE', 'COLUMN_NAME');

        $this->assertStringContainsString('bigint', $types['single_repo']);
        $this->assertStringContainsString('varchar', $types['multi_repo']);
    }
}

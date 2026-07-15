<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Repair migration for environments where the `artists` table was created
 * before the legacy migration chain was consolidated into
 * 2026_07_10_000001_create_artists_table.php.
 *
 * Because Laravel tracks migrations by filename (not content), an
 * environment that had already run the old chain (create → add profile
 * fields → add display contact → add semiannual reminder → rename → drop
 * legacy) will have that filename marked as migrated, but may be missing
 * columns if the consolidated migration file replaced a filename that was
 * already recorded as run before those columns existed.
 *
 * This migration is fully idempotent: it only adds columns that are
 * actually missing on both the `artists` table and its `_artists` audit
 * table, so it is a no-op on environments that already have the full
 * consolidated schema.
 */
return new class extends Migration
{
    /**
     * @var array<string, callable(Blueprint): void>
     */
    private array $columns;

    public function __construct()
    {
        $this->columns = [
            'rep_image' => fn (Blueprint $table) => $table->unsignedBigInteger('rep_image')->nullable(),
            'discipline_secondary' => fn (Blueprint $table) => $table->unsignedBigInteger('discipline_secondary')->nullable(),
            'activities' => fn (Blueprint $table) => $table->json('activities')->nullable(),
            'secondary_activities' => fn (Blueprint $table) => $table->json('secondary_activities')->nullable(),
            'keywords' => fn (Blueprint $table) => $table->json('keywords')->nullable(),
            'links' => fn (Blueprint $table) => $table->json('links')->nullable(),
            'collaborations' => fn (Blueprint $table) => $table->json('collaborations')->nullable(),
            'enum_status' => fn (Blueprint $table) => $table->string('enum_status', 255),
            'published_at' => fn (Blueprint $table) => $table->timestamp('published_at')->nullable(),
            'last_confirmed_at' => fn (Blueprint $table) => $table->timestamp('last_confirmed_at')->nullable(),
            'reminder_sent_at' => fn (Blueprint $table) => $table->timestamp('reminder_sent_at')->nullable(),
            'confirmation_token' => fn (Blueprint $table) => $table->string('confirmation_token', 64)->nullable(),
            'enum_show_contact' => fn (Blueprint $table) => $table->string('enum_show_contact', 255),
        ];
    }

    public function up(): void
    {
        $this->repair('artists');
        $this->repair('_artists');
    }

    private function repair(string $tableName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $missing = array_filter(
            array_keys($this->columns),
            fn (string $column) => ! Schema::hasColumn($tableName, $column)
        );

        if (empty($missing)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($missing): void {
            foreach ($missing as $column) {
                ($this->columns[$column])($table);
            }
        });
    }

    public function down(): void
    {
        // Intentionally left blank: this is a defensive repair migration,
        // rolling back would risk dropping columns other migrations depend on.
    }
};

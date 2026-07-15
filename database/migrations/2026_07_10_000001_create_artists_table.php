<?php

use App\Database\Schemas\Audit;
use App\Database\Schemas\Table;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Create the artists table (new data model — App\Database\Models\Artist).
 *
 * Consolidates:
 *  - 2026_07_10_000001_create_newartists_table.php
 *  - 2026_07_13_000000_add_profile_fields_to_artists_table.php
 *
 * The legacy migration chain (create artists/requests/changes → add columns
 * → rename → drop) has been removed. A fresh install no longer goes through
 * the legacy path at all.
 */
return new class extends Migration
{
    private string $tableName = 'artists';

    private bool $hasAudit = true;

    private function _columns(&$table): void
    {
        $table->bigIncrements('id');
        $table->foreignKey('registration_id', 'registrations', 'id', 'bigInteger');
        $table->foreignKey('user_id', 'users', 'id', 'bigInteger');
        $table->string('slug', 255)->unique();
        $table->string('artist_name', 255);
        $table->foreignKey('discipline_main_id', 'disciplines', 'id', 'bigInteger')->nullable();
        $table->string('email', 125)->nullable();
        $table->string('phone', 15)->nullable();
        $table->foreignKey('rep_image', 'repositories', 'id', 'bigInteger')->nullable();
        $table->text('biography')->nullable();
        $table->string('city', 125)->nullable();
        $table->foreignKey('discipline_secondary', 'disciplines', 'id', 'bigInteger')->nullable();
        // JSON profile fields
        $table->json('activities')->nullable();
        $table->json('secondary_activities')->nullable();
        $table->json('keywords')->nullable();
        $table->json('links')->nullable();
        $table->json('collaborations')->nullable();
        // Status
        $table->enumeration('enum_status', 'string');
        $table->timestamp('published_at')->nullable();
        // Semiannual confirmation
        $table->timestamp('last_confirmed_at')->nullable();
        $table->timestamp('reminder_sent_at')->nullable();
        $table->string('confirmation_token', 64)->nullable()->unique();
        // Contact display preference
        $table->enumeration('enum_show_contact', 'string');
    }

    public function up(): void
    {
        Table::make($this->tableName, function (&$table): void {
            $this->_columns($table);
        });

        if ($this->hasAudit) {
            Audit::make($this->tableName, function (&$table): void {
                $this->_columns($table);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists($this->tableName);
        if ($this->hasAudit) {
            Schema::dropIfExists('_'.$this->tableName);
        }
    }
};

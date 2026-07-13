<?php

use App\Database\Schemas\Table;
use App\Database\Schemas\Audit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;


return new class extends Migration {
    private string $tableName = 'newartists';
    private bool $hasAudit = true;

    private function _columns(&$table)
    {
        $table->date('published_at')->nullable()->after('enum_show_contact');
        $table->date('confirmed_at')->nullable()->after('published_at');
        $table->date('reminded_at')->nullable()->after('confirmed_at');
    }

    public function up(): void
    {
        $schema = DB::connection()->getSchemaBuilder();
        $schema->blueprintResolver(function ($table, $callback) {
            return new Table($table, $callback);
        });

        $schema->table($this->tableName, function ($table) {
            $this->_columns($table);
        });

        if ($this->hasAudit) {
            $schema->table('_' . $this->tableName, function ($table) {
                $this->_columns($table);
            });
        }
    }

    public function down(): void
    {
        $schema = DB::connection()->getSchemaBuilder();
        $schema->blueprintResolver(fn ($table, $callback) => new Table($table, $callback));

        $schema->table($this->tableName, function ($table) {
            $table->dropColumn(['published_at', 'confirmed_at', 'reminded_at']);
        });

        if ($this->hasAudit) {
            $schema->table('_' . $this->tableName, function ($table) {
                $table->dropColumn(['published_at', 'confirmed_at', 'reminded_at']);
            });
        }
    }
};

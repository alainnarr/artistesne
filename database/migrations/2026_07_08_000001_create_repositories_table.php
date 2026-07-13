<?php

use App\Database\Schemas\Table;
use App\Database\Schemas\Audit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private string $tableName = 'repositories';
    private bool $hasAudit = true;

    private function _columns(&$table)
    {
        $table->bigIncrements('id');
        $table->morphs('repositoryable');
        $table->string('name', 255);
        $table->string('file_type', 100);
        $table->unsignedInteger('size');
        $table->enumeration('enum_disk', 'string');
        $table->string('path', 255)->unique();
    }

    public function up(): void
    {
        Table::make($this->tableName, function (&$table) {
            $this->_columns($table);
        });

        if ($this->hasAudit) {
            Audit::make($this->tableName, function (&$table) {
                $this->_columns($table);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists($this->tableName);
        if ($this->hasAudit) {
            Schema::dropIfExists('_' . $this->tableName);
        }
    }
};

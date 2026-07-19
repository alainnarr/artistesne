<?php

use App\Database\Schemas\Table;
use App\Database\Schemas\Audit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private string $tableName = 'users';
    private bool $hasAudit = false;

    private function _columns(&$table)
    {
        $table->bigIncrements('id');
        $table->uuid('uuid')->unique();
        $table->string('email', 125)->unique();
        $table->string('name', 125);
        $table->enumeration('enum_role', 'string');
        $table->string('adfs_id', 255)->nullable()->unique();
        $table->string('magic_link_token', 255)->nullable();
        $table->timestamp('magic_link_sent_at')->nullable();
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

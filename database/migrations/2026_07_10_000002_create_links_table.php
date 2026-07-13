<?php

use App\Database\Schemas\Table;
use App\Database\Schemas\Audit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private string $tableName = 'links';
    private bool $hasAudit = true;

    private function _columns(&$table)
    {
        $table->bigIncrements('id');
        // $table->foreignKey('artist_id', 'artists', 'id', 'bigInteger')->nullable();
        $table->unsignedBigInteger('artist_id')->nullable();
        $table->foreignKey('registration_id', 'registrations', 'id', 'bigInteger')->nullable();
        $table->string('link', 255);
        $table->enumeration('enum_type', 'string');
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

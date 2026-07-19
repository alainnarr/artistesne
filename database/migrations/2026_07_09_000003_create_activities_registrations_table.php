<?php

use App\Database\Schemas\Audit;
use App\Database\Schemas\Table;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $tableName = 'activities_registrations';

    private bool $hasAudit = false;

    private function _columns(&$table)
    {
        $table->bigIncrements('id');
        $table->foreignKey('activity_id', 'activities', 'id', 'bigInteger');
        $table->foreignKey('registration_id', 'registrations', 'id', 'bigInteger');
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
            Schema::dropIfExists('_'.$this->tableName);
        }
    }
};

<?php

use App\Database\Schemas\Table;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $tableName = 'synonyms';

    private function _columns(&$table): void
    {
        $table->bigIncrements('id');
        $table->foreignKey('activity_id', 'activities', 'id', 'bigInteger');
        $table->string('label', 125);
    }

    public function up(): void
    {
        Table::make($this->tableName, function (&$table) {
            $this->_columns($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->tableName);
    }
};

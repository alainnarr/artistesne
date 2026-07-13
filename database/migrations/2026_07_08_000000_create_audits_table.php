<?php

use App\Database\Schemas\Table;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private string $tableName = 'audits';

    private function columns(&$table)
    {
        $table->bigIncrements('id');
        $table->string('fk_table', 64); // Name of the data source table
        $table->unsignedBigInteger('fk_id'); // Record id in the original table
        $table->json('data'); // Original data stored
    }

    public function up(): void
    {
        Table::make($this->tableName, function (&$table) {
            $this->columns($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->tableName);
    }
};

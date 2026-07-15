<?php

use App\Database\Schemas\Audit;
use App\Database\Schemas\Table;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $tableName = 'artist_change_requests';

    private bool $hasAudit = true;

    private function _columns(&$table)
    {
        $table->bigIncrements('id');
        $table->foreignKey('artist_id', 'artists', 'id', 'bigInteger');
        $table->foreignKey('submitted_by', 'users', 'id', 'bigInteger');
        $table->json('payload');
        $table->enumeration('status', 'string');
        $table->foreignKey('reviewed_by', 'users', 'id', 'bigInteger')->nullable();
        $table->dateTime('reviewed_at')->nullable();
        $table->text('review_notes')->nullable();
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

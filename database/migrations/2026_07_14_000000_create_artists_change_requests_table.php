<?php

use App\Database\Schemas\Table;
use App\Database\Schemas\Audit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private string $tableName = 'artists_change_requests';
    private bool $hasAudit = false;

    private function _columns(&$table)
    {
        $table->bigIncrements('id');
        // $table->foreignKey('artist_id', 'artists', 'id', 'bigInteger');
        $table->unsignedBigInteger('artist_id');
        $table->json('payload');
        $table->enumeration('enum_status', 'string');
        $table->dateTime('reviewed_at')->nullable();
        $table->foreignKey('reviewed_by', 'users', 'id', 'bigInteger')->nullable();
        $table->text('review_notes')->nullable();
        $table->softDeletes();
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

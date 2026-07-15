<?php

use App\Database\Schemas\Audit;
use App\Database\Schemas\Table;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $tableName = 'registrations';

    private bool $hasAudit = true;

    private function _columns(&$table)
    {
        $table->bigIncrements('id');
        $table->string('real_name', 125);
        $table->string('artist_name', 125);
        $table->string('url', 255)->nullable();
        $table->date('birth_date');
        $table->string('email', 125)->unique();
        $table->string('phone', 30);
        $table->string('residence_location', 125);
        $table->string('locality', 125)->nullable();
        $table->text('canton_link')->nullable();
        $table->foreignKey('discipline_main', 'disciplines', 'id', 'bigInteger');
        $table->foreignKey('discipline_secondary', 'disciplines', 'id', 'bigInteger')->nullable();
        $table->text('training')->nullable();
        $table->text('paid_work')->nullable();
        $table->text('recognition')->nullable();
        $table->text('recent_achievements')->nullable();
        $table->text('last_work')->nullable();
        $table->enumeration('enum_status', 'string');
        $table->dateTime('reviewed_at')->nullable();
        $table->foreignKey('reviewed_by', 'users', 'id', 'bigInteger')->nullable();
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

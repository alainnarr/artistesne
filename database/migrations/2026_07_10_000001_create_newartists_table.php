<?php

use App\Database\Schemas\Table;
use App\Database\Schemas\Audit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private string $tableName = 'newartists';
    private bool $hasAudit = true;

    private function _columns(&$table)
    {
        $table->bigIncrements('id');
        $table->foreignKey('registration_id', 'registrations', 'id', 'bigInteger');
        $table->foreignKey('user_id', 'users', 'id', 'bigInteger');
        $table->string('artist_name', 255);
        $table->string('email', 125)->nullable();
        $table->string('phone', 15)->nullable();
        $table->foreignKey('rep_image', 'repositories', 'id', 'bigInteger')->nullable();
        $table->text('biography')->nullable();
        $table->string('city', 125)->nullable();
        $table->foreignKey('discipline_secondary', 'disciplines', 'id', 'bigInteger')->nullable();
        $table->enumeration('enum_status', 'string');
        $table->enumeration('enum_show_contact', 'string');
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

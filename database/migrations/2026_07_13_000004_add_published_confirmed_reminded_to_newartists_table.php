<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    private string $tableName = 'newartists';
    private bool $hasAudit = true;

    private function _columns(&$table)
    {
        $table->date('published_at')->nullable()->after('enum_show_contact');
        $table->date('confirmed_at')->nullable()->after('published_at');
        $table->date('reminded_at')->nullable()->after('confirmed_at');
    }

    public function up(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $this->_columns($table);
        });

        if ($this->hasAudit) {
            Schema::table('_' . $this->tableName, function (Blueprint $table) {
                $this->_columns($table);
            });
        }
    }

    public function down(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropColumn(['published_at', 'confirmed_at', 'reminded_at']);
        });

        if ($this->hasAudit) {
            Schema::table('_' . $this->tableName, function (Blueprint $table) {
                $table->dropColumn(['published_at', 'confirmed_at', 'reminded_at']);
            });
        }
    }
};

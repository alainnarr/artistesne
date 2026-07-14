<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private string $tableName = 'registrations';

    public function up(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
        });
    }

    public function down(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->foreign('reviewed_by')->references('id')->on('users');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private string $tableName = 'newartists';

    public function up(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['rep_image']);
            $table->dropColumn(['rep_image']);
        });
    }

    public function down(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->unsignedBigInteger('rep_image')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('rep_image')->references('id')->on('repositories');
        });
    }
};

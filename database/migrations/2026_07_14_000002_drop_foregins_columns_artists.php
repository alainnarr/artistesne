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
            $table->dropIndex('newartists_user_id_foreign');

            $table->dropForeign(['rep_image']);
            $table->dropIndex('newartists_rep_image_foreign');
            $table->dropColumn(['rep_image']);
        });
    }

    public function down(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('rep_image')->nullable();
            $table->foreign('rep_image')->references('id')->on('repositories');
        });
    }
};

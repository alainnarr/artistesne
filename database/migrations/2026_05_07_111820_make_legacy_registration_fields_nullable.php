<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('artist_registration_requests', function (Blueprint $table) {
            $table->text('motivation')->nullable()->change();
            $table->string('discipline')->nullable()->change();
            $table->string('proof_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('artist_registration_requests', function (Blueprint $table) {
            $table->text('motivation')->nullable(false)->change();
            $table->string('discipline')->nullable(false)->change();
            $table->string('proof_url')->nullable(false)->change();
        });
    }
};

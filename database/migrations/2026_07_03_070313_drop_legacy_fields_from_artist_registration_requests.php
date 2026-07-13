<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the legacy registration fields that predate the current three-step
     * wizard. They are no longer populated nor displayed.
     */
    public function up(): void
    {
        Schema::table('artist_registration_requests', function (Blueprint $table) {
            $table->dropColumn(['discipline', 'motivation', 'proof_url']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artist_registration_requests', function (Blueprint $table) {
            $table->string('discipline')->nullable();
            $table->text('motivation')->nullable();
            $table->string('proof_url')->nullable();
        });
    }
};

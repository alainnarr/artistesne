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
        Schema::table('artists', function (Blueprint $table) {
            $table->timestamp('last_confirmed_at')->nullable()->after('published_at');
            $table->timestamp('reminder_sent_at')->nullable()->after('last_confirmed_at');
            $table->string('confirmation_token', 64)->nullable()->unique()->after('reminder_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->dropColumn(['last_confirmed_at', 'reminder_sent_at', 'confirmation_token']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * - adfs_id: stores the AD FS `sub` claim; null for artist accounts.
     * - password: made nullable; admin accounts authenticated via AD FS
     *   never need a local password.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('adfs_id')->nullable()->unique()->after('email');
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('adfs_id');
            $table->string('password')->nullable(false)->change();
        });
    }
};

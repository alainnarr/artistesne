<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->string('city')->nullable()->after('discipline');
            $table->string('secondary_discipline')->nullable()->after('city');
            $table->json('activities')->nullable()->after('secondary_discipline');
            $table->json('secondary_activities')->nullable()->after('activities');
            $table->json('keywords')->nullable()->after('secondary_activities');
            $table->json('collaborations')->nullable()->after('keywords');
        });
    }

    public function down(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->dropColumn([
                'city',
                'secondary_discipline',
                'activities',
                'secondary_activities',
                'keywords',
                'collaborations',
            ]);
        });
    }
};

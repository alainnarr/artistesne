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
            // Identity
            $table->string('full_name')->nullable()->after('artist_name');
            $table->date('birth_date')->nullable()->after('full_name');

            // Territoriality
            $table->string('residence_location')->nullable()->after('phone');
            $table->text('canton_link')->nullable()->after('residence_location');

            // Domain & activities
            $table->string('main_domain')->nullable()->after('discipline');
            $table->text('main_activities')->nullable()->after('main_domain');

            // Professionalism criteria
            $table->text('training')->nullable()->after('main_activities');
            $table->text('paid_activity')->nullable()->after('training');
            $table->text('recognition')->nullable()->after('paid_activity');
            $table->text('recent_achievement')->nullable()->after('recognition');

            // Temporality
            $table->text('last_activity')->nullable()->after('recent_achievement');

            // Supporting documents
            $table->text('documents_info')->nullable()->after('last_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artist_registration_requests', function (Blueprint $table) {
            $table->dropColumn([
                'full_name', 'birth_date', 'residence_location', 'canton_link',
                'main_domain', 'main_activities', 'training', 'paid_activity',
                'recognition', 'recent_achievement', 'last_activity', 'documents_info',
            ]);
        });
    }
};

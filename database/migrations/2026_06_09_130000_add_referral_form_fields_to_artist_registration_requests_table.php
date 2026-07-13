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
            // Identity options
            $table->boolean('show_artist_name')->default(false)->after('artist_name');
            $table->boolean('display_contact_button')->default(false)->after('email');

            // Territoriality
            $table->string('locality')->nullable()->after('residence_location');
            $table->string('commune')->nullable()->after('locality');

            // Domain & activities (single choice + free precision)
            $table->string('main_activity')->nullable()->after('main_domain');
            $table->string('main_activity_other')->nullable()->after('main_activity');

            // Supporting links & documents
            $table->json('links')->nullable()->after('documents_info');
            $table->json('documents')->nullable()->after('links');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artist_registration_requests', function (Blueprint $table) {
            $table->dropColumn([
                'show_artist_name',
                'display_contact_button',
                'locality',
                'commune',
                'main_activity',
                'main_activity_other',
                'links',
                'documents',
            ]);
        });
    }
};

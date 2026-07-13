<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a stable machine-readable identifier for `domain` terms (e.g.
     * "musique"), distinct from `name` which holds the display label (e.g.
     * "Musique"). Existing `main_activities`/`secondary_activities`/`keywords`
     * rows keep referencing a domain via this slug in their `domain` column.
     */
    public function up(): void
    {
        Schema::table('taxonomy_terms', function (Blueprint $table): void {
            $table->string('slug')->nullable()->after('domain');
            $table->unique(['type', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxonomy_terms', function (Blueprint $table): void {
            $table->dropUnique(['type', 'slug']);
            $table->dropColumn('slug');
        });
    }
};

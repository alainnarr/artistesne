<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artist_registration_requests', function (Blueprint $table) {
            $table->id();
            $table->string('artist_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('discipline')->nullable();
            $table->text('motivation');
            $table->string('proof_url')->nullable();
            $table->string('status')->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_registration_requests');
    }
};

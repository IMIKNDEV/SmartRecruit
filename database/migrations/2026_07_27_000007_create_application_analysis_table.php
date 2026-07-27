<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_analysis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_offer_id')->constrained()->cascadeOnDelete();
            $table->decimal('matching_score', 5, 2)->default(0);
            $table->json('matched_keywords')->nullable();
            $table->json('missing_keywords')->nullable();
            $table->text('strengths')->nullable();
            $table->text('gaps')->nullable();
            $table->unsignedTinyInteger('years_experience')->nullable();
            $table->string('education_level')->nullable();
            $table->json('languages')->nullable();
            $table->text('recommendation')->nullable();
            $table->timestamps();
            $table->index('matching_score');
            $table->index('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_analysis');
    }
};

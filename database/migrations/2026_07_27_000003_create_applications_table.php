<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('job_offer_id')->constrained()->cascadeOnDelete();
            $table->string('cv_path');
            $table->text('cover_letter');
            $table->json('tags')->nullable();
            $table->enum('status', ['received', 'interview', 'accepted', 'refused'])->default('received');
            $table->text('notes')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->unique(['candidate_id', 'job_offer_id']);
            $table->index(['candidate_id', 'job_offer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};

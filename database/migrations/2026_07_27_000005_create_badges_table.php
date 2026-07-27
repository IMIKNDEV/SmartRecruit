<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['cv_complet', 'high_match', 'interview_passed']);
            $table->timestamp('awarded_at')->useCurrent();
            $table->timestamps();
            $table->unique(['candidate_id', 'type']);
            $table->index('candidate_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};

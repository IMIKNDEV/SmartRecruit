<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruiter_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->json('criteria');
            $table->timestamps();
            $table->index('recruiter_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_filters');
    }
};

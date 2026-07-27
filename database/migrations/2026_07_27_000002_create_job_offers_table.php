<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruiter_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('tech_stack', 500);
            $table->enum('contract_type', ['CDI', 'CDD', 'Stage', 'Alternance', 'Freelance']);
            $table->decimal('salary', 10, 2)->nullable();
            $table->date('deadline');
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['recruiter_id', 'status', 'deadline']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_offers');
    }
};

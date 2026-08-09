<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Soft-delete applications. A deleted application disappears from the
     * recruiter's application list and per-job pipeline, but its row stays in
     * the database so the analytical dashboard keeps counting it in funnels,
     * time-to-hire, score distribution, etc.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};

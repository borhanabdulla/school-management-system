<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subject_grading_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grading_template_id')->constrained()->cascadeOnDelete();

            $table->decimal('max_score', 8, 2)->default(100);
            $table->decimal('pass_score', 8, 2)->default(50);
            $table->boolean('is_continuous')->default(true);
            $table->boolean('counts_in_gpa')->default(true);

            $table->timestamps();

            // Ensure unique config per subject/grade/term
            $table->unique(['subject_id', 'grade_id', 'term_id'], 'subj_grade_term_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_grading_configs');
    }
};

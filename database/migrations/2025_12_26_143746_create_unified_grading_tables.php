<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Grading Templates
        Schema::create('grading_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('total_max_score', 8, 2)->default(100);
            $table->decimal('pass_score', 8, 2)->default(50);
            $table->enum('rounding_rule', ['none', 'nearest_integer', 'up', 'down'])->default('none');
            $table->integer('rounding_precision')->default(0); // 0, 1, 2
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('grade_id')->nullable()->constrained()->nullOnDelete(); // Optional: link to specific grade
            $table->timestamps();
        });

        // 2. Template Categories (Hierarchical)
        Schema::create('template_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grading_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('template_categories')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('weight', 8, 2); // The weighted value (e.g., 20%)
            $table->decimal('max_raw_score', 8, 2)->nullable(); // Optional raw score limit
            $table->enum('calculation_type', ['sum', 'average', 'weighted_average'])->default('sum');
            $table->boolean('is_dynamic_weight')->default(false); // If true, splits weight among children
            $table->boolean('is_locked')->default(false); // If true, teacher cannot change weights
            $table->boolean('pass_required')->default(false); // If true, student must pass this category
            $table->decimal('pass_threshold', 8, 2)->nullable(); // Specific pass score for this category
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 3. Assessments (Teacher created items)
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_category_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->decimal('max_score', 8, 2); // Raw max score (e.g., 40)
            $table->decimal('weight', 8, 2)->nullable(); // Weight inside the category (if applicable)
            $table->date('due_date')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        // 4. Student Marks
        Schema::create('student_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('template_category_id')->nullable()->constrained()->cascadeOnDelete(); // For direct category grading

            $table->decimal('raw_score', 8, 2)->nullable(); // The actual score entered
            $table->decimal('scaled_score', 8, 2)->nullable(); // The calculated weighted score (Performance optimization)

            $table->boolean('is_missing')->default(false);
            $table->boolean('is_excused')->default(false);
            $table->text('feedback')->nullable();
            $table->foreignId('graded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Ensure unique mark per student per assessment/category
            $table->unique(['student_id', 'assessment_id']);
            $table->unique(['student_id', 'template_category_id']); // If grading directly on category
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_marks');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('template_categories');
        Schema::dropIfExists('grading_templates');
    }
};

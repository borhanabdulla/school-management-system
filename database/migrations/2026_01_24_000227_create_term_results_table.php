<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('term_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_offering_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();

            $table->decimal('coursework_score', 6, 2)->default(0);
            $table->decimal('exam_score', 6, 2)->default(0);
            $table->decimal('total_score', 6, 2)->default(0);
            $table->decimal('max_score', 6, 2)->default(100);
            $table->decimal('percentage', 5, 2)->default(0);

            $table->string('grade_letter')->nullable();
            $table->boolean('is_passed')->default(false);
            $table->timestamp('calculated_at')->nullable();

            $table->timestamps();

            $table->unique(['student_id', 'course_offering_id', 'term_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('term_results');
    }
};

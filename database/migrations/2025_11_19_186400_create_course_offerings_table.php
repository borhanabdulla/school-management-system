<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('course_offerings', function (Blueprint $table) {
            $table->id();
            
            // Academic Year is required, Term is optional
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('term_id')->nullable()->constrained()->cascadeOnDelete();
            
            // Subject and Class Section
            $table->foreignId('subject_id')->constrained();
            $table->foreignId('class_section_id')->constrained();
            
            // Teacher assignment
            $table->foreignId('teacher_id')->constrained('teachers', 'id');
            
            $table->timestamps();
            
            // Unique constraint: one teacher per subject per section per academic year
            $table->unique(['academic_year_id', 'subject_id', 'class_section_id'], 'offering_unique');
            
            // Indexes for performance
            $table->index('academic_year_id');
            $table->index('term_id');
            $table->index('teacher_id');
            $table->index(['class_section_id', 'academic_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_offerings');
    }
};

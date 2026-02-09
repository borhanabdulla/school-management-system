<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * جدول الربط بين الصفوف والقوالب:
     * - كل صف يمكن أن يكون له قالب واحد فقط في نفس السنة الدراسية
     */
    public function up(): void
    {
        Schema::create('grade_timetable_template', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('grade_id')
                  ->constrained()
                  ->cascadeOnDelete();
            
            $table->foreignId('template_id')
                  ->constrained('timetable_templates')
                  ->cascadeOnDelete();
            
            $table->foreignId('academic_year_id')
                  ->constrained()
                  ->cascadeOnDelete();
            
            $table->timestamps();
            
            // قيد فريد: صف واحد ← قالب واحد في نفس السنة
            $table->unique(['grade_id', 'academic_year_id'], 'grade_year_unique');
            
            // فهارس للبحث
            $table->index('template_id');
            $table->index('academic_year_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_timetable_template');
    }
};

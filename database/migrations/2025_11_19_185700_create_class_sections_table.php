<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sections', function (Blueprint $table) {
            $table->id();
            // الحقول الإلزامية (لا تقبل null)
            $table->string('name');
            $table->foreignId('grade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            
            $table->unsignedInteger('max_capacity')->default(30);
            $table->enum('gender_type', ['boys', 'girls', 'mixed'])->default('mixed');
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();

            // القيد الذهبي: منع تكرار اسم الشعبة (أ) للصف (الأول) في السنة (2024)
            $table->unique(['name', 'grade_id', 'academic_year_id'], 'section_unique_constraint');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sections');
    }
};
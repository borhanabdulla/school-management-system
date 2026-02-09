<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. بنك المواد (Subjects Library)
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // اسم المادة (رياضيات، فيزياء)
            $table->string('code')->nullable()->unique(); // MATH101
            $table->enum('type', ['theory', 'practical', 'both'])->default('theory');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. توزيع المناهج (Curriculum)
        Schema::create('grade_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            
            // الإعدادات الأكاديمية
            $table->integer('credit_hours')->default(1); // الوزن / عدد الحصص
            $table->integer('max_grade')->default(100);  // الدرجة العظمى
            $table->integer('pass_grade')->default(50);  // درجة النجاح
            
            // توقيت التدريس (يسمح بتكرار المادة في ترمات مختلفة)
            $table->enum('term_type', ['full_year', 'term_1', 'term_2', 'term_3'])->default('full_year');
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // القيد المركب: نمنع تكرار (المادة + الصف + الترم)
            // يعني: مسموح رياضيات ترم 1، ورياضيات ترم 2
            // ممنوع: رياضيات ترم 1 مرتين لنفس الصف
            $table->unique(['grade_id', 'subject_id', 'term_type'], 'unique_subject_allocation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_subjects');
        Schema::dropIfExists('subjects');
    }
};
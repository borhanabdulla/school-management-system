<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. المراحل الدراسية (ابتدائي، متوسط...)
        Schema::create('educational_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('rank')->unique(); // ترتيب المرحلة (1, 2, 3)
            $table->decimal('min_passing_percentage', 5, 2)->default(50.00);
            $table->enum('grading_system', ['standard', 'gpa'])->default('standard');
            $table->timestamps();
        });

        // 2. الصفوف الدراسية (أول، ثاني...)
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('educational_stage_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('level_order'); // ترتيب الصف داخل المرحلة
            
            // سلسلة الترفيع: إلى أين يذهب الطالب الناجح؟
            // nullOnDelete مهم جداً هنا لمنع الأخطاء عند حذف صف مستقبلي
            $table->foreignId('next_grade_id')->nullable()->constrained('grades')->nullOnDelete();
            
            $table->timestamps();
            
            // منع تكرار اسم الصف في نفس المرحلة
            $table->unique(['educational_stage_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
        Schema::dropIfExists('educational_stages');
    }
};
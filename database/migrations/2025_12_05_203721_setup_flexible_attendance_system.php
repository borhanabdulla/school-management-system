<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. إضافة مربي الفصل للشعب
        Schema::table('class_sections', function (Blueprint $table) {
            $table->foreignId('homeroom_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
        });

        // 2. إعدادات الحضور
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            
            // النمط: daily_only (يومي), per_period (حصص), checkpoints (نقاط)
            $table->string('mode')->default('checkpoints'); 
            
            // المسؤول: class_teacher, subject_teacher, admin_staff
            $table->string('responsible_role')->default('subject_teacher');
            
            $table->integer('late_tolerance')->default(15); // دقائق السماحية
            
            $table->timestamps();
        });

        // 3. تعديل الخانات الزمنية (لتحديد نقاط التفتيش)
        Schema::table('time_slots', function (Blueprint $table) {
            $table->boolean('is_attendance_checkpoint')->default(false);
        });

        // 4. سجلات الحضور
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_section_id')->constrained()->cascadeOnDelete(); // للتسريع
            
            $table->date('date')->index();
            $table->foreignId('time_slot_id')->nullable()->constrained()->nullOnDelete();
            
            // حالة الحضور
            $table->enum('status', ['present', 'absent', 'late', 'excused', 'escaped'])->default('present');
            
            // بيانات إضافية
            $table->text('remarks')->nullable();
            $table->integer('delay_minutes')->default(0);
            
            // من رصد؟
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();

            // الفهارس والقيود
            $table->index(['class_section_id', 'date']); 
            // قيد يمنع تكرار سجل لنفس الطالب في نفس الحصة لنفس اليوم
            $table->unique(['student_id', 'date', 'time_slot_id'], 'attendance_unique_record');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::table('time_slots', function (Blueprint $table) {
            $table->dropColumn('is_attendance_checkpoint');
        });
        Schema::dropIfExists('attendance_settings');
        Schema::table('class_sections', function (Blueprint $table) {
            $table->dropForeign(['homeroom_teacher_id']);
            $table->dropColumn('homeroom_teacher_id');
        });
    }
};

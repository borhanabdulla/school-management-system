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
        // 1. جدول الدورات الامتحانية (Exam Sessions)
        Schema::create('exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // مثال: "اختبارات الفصل الأول 2024"
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['setup', 'active', 'processing', 'published', 'closed'])->default('setup');
            $table->boolean('is_active')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['academic_year_id', 'term_id', 'name']);
        });

        // 2. جدول اللجان (اختياري - للاستخدام المستقبلي)
        Schema::create('exam_committees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // مثال: "لجنة القاعة 1"
            $table->string('room')->nullable(); // رقم/اسم القاعة
            $table->integer('capacity')->default(30);
            $table->foreignId('supervisor_id')->nullable()->constrained('staff')->nullOnDelete(); // المشرف
            $table->timestamps();
        });

        // 3. جدول الجلوس والأرقام السرية (القلب الأمني)
        Schema::create('exam_seatings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('committee_id')->nullable()->constrained('exam_committees')->nullOnDelete();

            $table->string('seat_number'); // رقم الجلوس (علني - يكتبه الطالب)
            $table->string('secret_number'); // الرقم السري (مخفي - للكنترول فقط)

            $table->boolean('is_barred')->default(false); // حالة الحرمان
            $table->string('barred_reason')->nullable();

            $table->timestamps();

            // ضمان فرادة الأرقام داخل الدورة الواحدة
            $table->unique(['exam_session_id', 'seat_number']);
            $table->unique(['exam_session_id', 'secret_number']);
            $table->unique(['exam_session_id', 'student_id']); // طالب واحد = سجل واحد
        });

        // 4. جدول درجات الكنترول (الرصد الأعمى)
        Schema::create('control_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_seating_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_offering_id')->constrained()->cascadeOnDelete();

            $table->decimal('score', 5, 2)->nullable(); // الدرجة
            $table->boolean('is_absent')->default(false); // غياب عن الورقة

            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete(); // من رصد؟
            $table->foreignId('audited_by')->nullable()->constrained('users')->nullOnDelete(); // من راجع؟
            $table->timestamp('audited_at')->nullable();

            $table->timestamps();

            // طالب واحد = درجة واحدة لكل مادة
            $table->unique(['exam_seating_id', 'course_offering_id']);
        });

        // 5. جدول النتائج النهائية (المصب)
        Schema::create('final_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_offering_id')->constrained()->cascadeOnDelete();

            $table->decimal('coursework_score', 5, 2)->default(0); // درجة أعمال السنة (من المعلم)
            $table->decimal('final_exam_score', 5, 2)->default(0); // درجة الورقة (من الكنترول)
            $table->decimal('total_score', 5, 2)->default(0); // المجموع
            $table->decimal('grace_marks', 5, 2)->default(0); // درجات الرأفة (إن وجدت)

            $table->string('grade_label')->nullable(); // التقدير: ممتاز، جيد جداً...
            $table->enum('status', ['pass', 'fail', 'incomplete', 'absent', 'pending'])->default('pending');

            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->unique(['exam_session_id', 'student_id', 'course_offering_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('final_results');
        Schema::dropIfExists('control_marks');
        Schema::dropIfExists('exam_seatings');
        Schema::dropIfExists('exam_committees');
        Schema::dropIfExists('exam_sessions');
    }
};

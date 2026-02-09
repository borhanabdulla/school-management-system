<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // الأشهر الدراسية لكل ترم
        Schema::create('gradebook_months', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // مثل: "أكتوبر", "نوفمبر", "ديسمبر"
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // درجات الطلاب الشهرية
        Schema::create('monthly_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_offering_id')->constrained()->cascadeOnDelete();
            $table->foreignId('gradebook_month_id')->constrained()->cascadeOnDelete();

            $table->string('category'); // "تحريري", "شفهي", "واجبات", "حضور", أو مخصص
            $table->decimal('score', 5, 2)->nullable();
            $table->decimal('max_score', 5, 2)->default(10);

            $table->text('notes')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // منع التكرار
            $table->unique(['student_id', 'course_offering_id', 'gradebook_month_id', 'category'], 'unique_monthly_grade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_grades');
        Schema::dropIfExists('gradebook_months');
    }
};

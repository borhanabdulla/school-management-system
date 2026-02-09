<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique(); // رقم مرجعي للطلب
            $table->foreignId('academic_year_id')->constrained(); // لأي سنة يتقدم؟
            $table->foreignId('target_grade_id')->constrained('grades'); // لأي صف؟
            
            // بيانات المتقدم المبدئية
            $table->string('student_national_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            
            // بيانات التواصل
            $table->string('guardian_name');
            $table->string('guardian_phone');
            $table->string('guardian_email')->nullable();

            // حالة الطلب
            $table->enum('status', ['new', 'under_review', 'interview_scheduled', 'accepted', 'rejected', 'waitlist'])->default('new');
            
            // نتائج التقييم
            $table->text('interview_notes')->nullable();
            $table->decimal('entrance_exam_score', 5, 2)->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_applications');
    }
};

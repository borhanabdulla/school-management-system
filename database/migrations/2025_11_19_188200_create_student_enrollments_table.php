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
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained();
            $table->foreignId('grade_id')->constrained();
            $table->foreignId('class_section_id')->nullable()->constrained(); // الشعبة
            
            $table->date('enrollment_date');
            $table->date('drop_date')->nullable();
            
            // حالة القيد في هذه السنة تحديداً
            $table->enum('enrollment_type', ['new', 'returning', 'transfer_in']); // جديد، عائد، منقول
            $table->enum('status', ['active', 'completed', 'failed', 'withdrawn'])->default('active');
            
            $table->unique(['student_id', 'academic_year_id']); // لا يسجل مرتين في نفس السنة
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};

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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('admission_application_id')->nullable()->constrained()->nullOnDelete(); // ربط بالطلب السابق
            $table->string('admission_number')->unique(); // الرقم الأكاديمي الرسمي
            
            // البيانات الشخصية
            $table->string('first_name_ar');
            $table->string('family_name_ar');
            $table->string('first_name_en')->nullable();
            $table->string('family_name_en')->nullable();
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female']);
            $table->foreignId('nationality_id')->nullable()->constrained('countries');
            $table->string('national_id')->unique();
            $table->string('passport_number')->nullable();
            $table->string('blood_type')->nullable();
            
            // الحالة الأكاديمية الحالية (للسهولة)
            $table->foreignId('current_grade_id')->nullable()->constrained('grades');
            $table->foreignId('current_class_section_id')->nullable()->constrained('class_sections');
            
            $table->enum('status', ['active', 'graduated', 'withdrawn', 'suspended'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

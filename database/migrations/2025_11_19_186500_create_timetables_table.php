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
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('class_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('time_slot_id')->constrained()->cascadeOnDelete();
            
            // نربط بـ CourseOffering (المادة + المعلم)
            $table->foreignId('course_offering_id')->nullable()->constrained()->nullOnDelete();
            
            // (اختياري) الغرفة/المختبر
            $table->foreignId('facility_id')->nullable()->constrained('facilities')->nullOnDelete();
            
            $table->timestamps();

            // منع تكرار حصتين لنفس الشعبة في نفس الوقت
            $table->unique(['class_section_id', 'time_slot_id'], 'unique_section_slot');
            
            // فهرس للبحث السريع عن جدول المعلم
            $table->index('course_offering_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetables');
    }
};

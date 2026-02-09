<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_previous_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('school_name');
            $table->string('previous_curriculum')->nullable(); // وزاري، أمريكي، بريطاني
            $table->string('last_grade_completed'); // آخر صف أنهاه
            $table->year('completion_year');
            $table->float('last_gpa')->nullable(); // المعدل
            $table->text('reason_for_transfer')->nullable();
            $table->string('conduct_summary')->nullable(); // ملخص السلوك
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_previous_histories');
    }
};

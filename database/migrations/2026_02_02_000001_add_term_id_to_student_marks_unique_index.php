<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_marks', function (Blueprint $table) {
            $table->dropUnique('student_marks_student_course_category_unique');
            $table->unique(
                ['student_id', 'course_offering_id', 'template_category_id', 'term_id'],
                'student_marks_student_course_category_term_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('student_marks', function (Blueprint $table) {
            $table->dropUnique('student_marks_student_course_category_term_unique');
            $table->unique(
                ['student_id', 'course_offering_id', 'template_category_id'],
                'student_marks_student_course_category_unique'
            );
        });
    }
};

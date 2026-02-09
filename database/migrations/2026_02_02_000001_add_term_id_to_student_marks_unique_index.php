<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('student_marks', 'term_id')) {
            throw new RuntimeException('student_marks.term_id is required before updating the unique index.');
        }

        DB::statement("
            UPDATE student_marks
            SET term_id = (
                SELECT term_id
                FROM course_offerings
                WHERE course_offerings.id = student_marks.course_offering_id
            )
            WHERE term_id IS NULL AND course_offering_id IS NOT NULL
        ");

        $missingTerm = DB::table('student_marks')
            ->whereNull('term_id')
            ->whereNotNull('course_offering_id')
            ->first();

        if ($missingTerm) {
            throw new RuntimeException('student_marks.term_id backfill incomplete; aborting unique index change.');
        }

        $duplicate = DB::table('student_marks')
            ->select('student_id')
            ->groupBy('student_id', 'course_offering_id', 'template_category_id', 'term_id')
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicate) {
            throw new RuntimeException('Duplicate student_marks rows found for the same student/offering/category/term.');
        }

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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_grades', function (Blueprint $table) {
            $table->dropUnique('unique_monthly_grade_key');
            $table->unique(
                ['student_id', 'course_offering_id', 'gradebook_month_id', 'template_category_id'],
                'unique_monthly_grade_template'
            );
        });
    }

    public function down(): void
    {
        Schema::table('monthly_grades', function (Blueprint $table) {
            $table->dropUnique('unique_monthly_grade_template');
            $table->unique(
                ['student_id', 'course_offering_id', 'gradebook_month_id', 'category_key'],
                'unique_monthly_grade_key'
            );
        });
    }
};

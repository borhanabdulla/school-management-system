<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('monthly_grades')) {
            return;
        }

        if (! Schema::hasColumn('monthly_grades', 'category_key')) {
            return;
        }

        Schema::table('monthly_grades', function (Blueprint $table) {
            $table->dropUnique('unique_monthly_grade');
            $table->unique(
                ['student_id', 'course_offering_id', 'gradebook_month_id', 'category_key'],
                'unique_monthly_grade_key'
            );
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('monthly_grades')) {
            return;
        }

        Schema::table('monthly_grades', function (Blueprint $table) {
            $table->dropUnique('unique_monthly_grade_key');
            $table->unique(
                ['student_id', 'course_offering_id', 'gradebook_month_id', 'category'],
                'unique_monthly_grade'
            );
        });
    }
};

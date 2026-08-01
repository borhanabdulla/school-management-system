<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_grades', function (Blueprint $table) {
            $table->string('category_key')->nullable()->after('category');
            $table->index(
                ['course_offering_id', 'gradebook_month_id', 'student_id', 'category_key'],
                'monthly_grades_category_key_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('monthly_grades', function (Blueprint $table) {
            $table->dropIndex('monthly_grades_category_key_index');
            $table->dropColumn('category_key');
        });
    }
};

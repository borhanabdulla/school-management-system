<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_grades', function (Blueprint $table) {
            $table->foreignId('template_category_id')
                ->nullable()
                ->after('category_key')
                ->constrained('template_categories')
                ->nullOnDelete();
            $table->index(
                ['course_offering_id', 'gradebook_month_id', 'student_id', 'template_category_id'],
                'monthly_grades_template_category_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('monthly_grades', function (Blueprint $table) {
            $table->dropForeign(['template_category_id']);
            $table->dropIndex('monthly_grades_template_category_index');
            $table->dropColumn('template_category_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('course_offerings', function (Blueprint $table) {
            $table->dropUnique('offering_unique');
        });

        Schema::table('course_offerings', function (Blueprint $table) {
            $table->unique(
                ['academic_year_id', 'term_id', 'subject_id', 'class_section_id'],
                'offering_unique_term'
            );
        });
    }

    public function down(): void
    {
        Schema::table('course_offerings', function (Blueprint $table) {
            $table->dropUnique('offering_unique_term');
        });

        Schema::table('course_offerings', function (Blueprint $table) {
            $table->unique(
                ['academic_year_id', 'subject_id', 'class_section_id'],
                'offering_unique'
            );
        });
    }
};

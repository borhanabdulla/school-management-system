<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_marks', function (Blueprint $table) {
            if (!Schema::hasColumn('student_marks', 'academic_year_id')) {
                $table->foreignId('academic_year_id')
                    ->nullable()
                    ->after('course_offering_id')
                    ->constrained()
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('student_marks', 'term_id')) {
                $table->foreignId('term_id')
                    ->nullable()
                    ->after('academic_year_id')
                    ->constrained()
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_marks', function (Blueprint $table) {
            if (Schema::hasColumn('student_marks', 'term_id')) {
                $table->dropConstrainedForeignId('term_id');
            }
            if (Schema::hasColumn('student_marks', 'academic_year_id')) {
                $table->dropConstrainedForeignId('academic_year_id');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_marks', function (Blueprint $table) {
            $table->foreignId('academic_year_id')
                ->nullable()
                ->after('course_offering_id')
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('term_id')
                ->nullable()
                ->after('academic_year_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_marks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('term_id');
            $table->dropConstrainedForeignId('academic_year_id');
        });
    }
};

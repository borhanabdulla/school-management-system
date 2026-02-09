<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('course_offerings', function (Blueprint $table) {
            // Drop old unique constraint if it exists
            try {
                $table->dropUnique('offering_unique');
            } catch (\Throwable $e) {
                // Ignore if it doesn't exist (e.g., already dropped or different name)
            }

            // Create new unique constraint including term_id
            // Note: Since term_id is nullable, we rely on application logic to ensure uniqueness for non-null terms,
            // OR we rely on DBs that treat NULLs as unique (SQLite/MySQL default).
            // BUT, for PR0, we want to allow multiple offerings for same subject/section IF term is different.
            $table->unique(
                ['academic_year_id', 'term_id', 'subject_id', 'class_section_id'],
                'offering_unique_term'
            );
        });
    }

    public function down(): void
    {
        Schema::table('course_offerings', function (Blueprint $table) {
            try {
                $table->dropUnique('offering_unique_term');
            } catch (\Throwable $e) {
            }

            // Restore old unique (this might fail if data has duplicates now)
            $table->unique(
                ['academic_year_id', 'subject_id', 'class_section_id'],
                'offering_unique'
            );
        });
    }
};

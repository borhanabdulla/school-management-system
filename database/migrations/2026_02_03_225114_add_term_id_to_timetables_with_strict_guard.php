<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add term_id column (Nullable initially)
        Schema::table('timetables', function (Blueprint $table) {
            $table->foreignId('term_id')->nullable()->after('class_section_id')->constrained()->cascadeOnDelete();
            // Add temporary index for performance during backfill
            $table->index(['class_section_id', 'term_id']);
        });

        // 2. Backfill Data
        // Update timetables setting term_id from the associated course_offerings.term_id
        // Using standard SQL subquery for SQLite compatibility
        DB::statement("
            UPDATE timetables
            SET term_id = (
                SELECT term_id
                FROM course_offerings
                WHERE course_offerings.id = timetables.course_offering_id
            )
            WHERE course_offering_id IS NOT NULL
        ");

        // 3. Strict Guard (Safety Check)
        // Verify no orphaned records exist (term_id is null)
        $orphans = DB::table('timetables')->whereNull('term_id')->count();

        if ($orphans > 0) {
            // Rollback the column addition to leave DB clean
            Schema::table('timetables', function (Blueprint $table) {
                $table->dropForeign(['term_id']);
                $table->dropColumn('term_id');
            });

            throw new \RuntimeException(
                "Migration Aborted: Found {$orphans} timetable entries with unresolved Term ID. " .
                "This happens if timetables exist without a CourseOffering, or the Offering has no Term. " .
                "Please fix data manually before running this migration."
            );
        }

        // 4. Enforce Not Null
        Schema::table('timetables', function (Blueprint $table) {
            $table->foreignId('term_id')->nullable(false)->change();
        });

        // 5. Update Constraints
        Schema::table('timetables', function (Blueprint $table) {
            // Drop old unique constraint
            $table->dropUnique('unique_section_slot');

            // Create new unique constraint
            $table->unique(['class_section_id', 'time_slot_id', 'term_id'], 'unique_section_slot_term');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            // Drop new unique constraint first
            $table->dropUnique('unique_section_slot_term');
        });

        Schema::table('timetables', function (Blueprint $table) {
            // Drop the index on (class_section_id, term_id) first
            $table->dropIndex('timetables_class_section_id_term_id_index');
        });

        Schema::table('timetables', function (Blueprint $table) {
            // Drop foreign key
            $table->dropForeign(['term_id']);
        });

        Schema::table('timetables', function (Blueprint $table) {
            // Drop the column
            $table->dropColumn('term_id');
        });

        // Restore old unique constraint separately
        Schema::table('timetables', function (Blueprint $table) {
            $table->unique(['class_section_id', 'time_slot_id'], 'unique_section_slot');
        });
    }
};

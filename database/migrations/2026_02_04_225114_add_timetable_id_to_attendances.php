<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add timetable_id to attendances table
 * 
 * This migration adds a foreign key reference from attendances to timetables,
 * enabling proper data integrity and audit trails.
 * 
 * Important: This is a Phase 1 migration that requires Phase 0 audit to pass.
 * 
 * After migration:
 * - Run: php artisan timetable:audit
 * - Run: php artisan timetable:backfill-attendance-timetable-id
 * 
 * @see docs/timetable/EXECUTION_PLAN.md
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add timetable_id column as nullable first
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('timetable_id')
                ->nullable()
                ->after('time_slot_id')
                ->constrained()
                ->nullOnDelete();
        });

        // 2. Add indexes for performance
        Schema::table('attendances', function (Blueprint $table) {
            // Index for foreign key lookups
            $table->index('timetable_id');
            
            // Composite index for common queries (class section + term + date)
            $table->index(['class_section_id', 'term_id', 'date']);
        });

        // 3. Add unique constraint to prevent duplicate attendance records
        // Using a manually named index to avoid MySQL's 64-char limit issues
        Schema::table('attendances', function (Blueprint $table) {
            // This will create: attendance_student_date_timetable_unique
            $table->unique(
                ['student_id', 'date', 'timetable_id'],
                'attendance_student_date_timetable_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Note: This rollback is destructive. In production, you should:
     * 1. Backup your data first
     * 2. Consider if the unique constraint has prevented duplicates
     */
    public function down(): void
    {
        // 1. Drop unique constraint
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendance_student_date_timetable_unique');
        });

        // 2. Drop indexes
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['class_section_id', 'term_id', 'date']);
            $table->dropIndex(['timetable_id']);
        });

        // 3. Drop foreign key and column
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['timetable_id']);
            $table->dropColumn('timetable_id');
        });
    }
};

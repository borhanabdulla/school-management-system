<?php

declare(strict_types=1);

namespace App\Console\Commands\DataAudit;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * TimetableDataAuditCommand
 * 
 * Phase 0.2: Preflight Data Audit Command
 * 
 * This command performs comprehensive data integrity checks for the timetable system
 * before running migrations. It validates:
 * 
 * 1. Orphan Records:
 *    - Attendance records referencing non-existent time_slots
 *    - Substitution records referencing non-existent timetables
 *    - Timetable entries referencing non-existent templates
 *    - Timetable entries referencing non-existent time_slots
 * 
 * 2. Data Integrity Issues:
 *    - Duplicate attendance records
 *    - Inconsistent data patterns
 *    - Missing required foreign keys
 * 
 * 3. Phase 1 Readiness:
 *    - Checks if timetable_id column exists
 *    - Validates foreign key constraints
 *    - Estimates backfill scope
 * 
 * Usage:
 *   php artisan timetable:audit
 *   php artisan timetable:audit --verbose
 *   php artisan timetable:audit --format=json (for CI/CD)
 *   php artisan timetable:audit --fix (apply safe fixes where possible)
 * 
 * @see docs/timetable/EXECUTION_PLAN.md
 * @see docs/timetable/DEEP_REVIEW.md
 */
class TimetableDataAuditCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'timetable:audit
                           {--fix : Apply safe fixes automatically}
                           {--format=console : Output format (console, json)}
                           {--skip-orphans : Skip orphan record checks}
                           {--skip-integrity : Skip data integrity checks}
                           {--skip-phase1 : Skip Phase 1 readiness checks}
                           {--verbose : Show detailed output}';

    /**
     * The console command description.
     */
    protected $description = 'Comprehensive data audit for timetable system (Phase 0.2)';

    /**
     * Audit results storage.
     */
    private array $results = [];
    private int $exitCode = 0;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Timetable Data Audit - Phase 0.2');
        $this->info('========================================');
        $this->newLine();

        // Initialize results
        $this->results = [
            'timestamp' => now()->toISOString(),
            'status' => 'passed',
            'checks' => [],
            'summary' => [
                'passed' => 0,
                'warnings' => 0,
                'errors' => 0,
            ],
        ];

        // Run audit phases
        $this->auditOrphanRecords();
        $this->auditDataIntegrity();
        $this->auditPhase1Readiness();

        // Display results
        $this->displayResults();

        // Apply fixes if requested
        if ($this->option('fix')) {
            $this->applySafeFixes();
        }

        // Set exit code based on results
        $this->exitCode = $this->results['summary']['errors'] > 0 ? Command::FAILURE : Command::SUCCESS;

        return $this->exitCode;
    }

    /**
     * Audit Phase 1: Check for orphan records.
     */
    private function auditOrphanRecords(): void
    {
        $this->info('📋 Phase 1: Orphan Record Audit');
        $this->output->newLine();

        $orphans = [];

        // Check 1: Attendance records with invalid time_slot_id
        $orphanAttendance = DB::scalar("
            SELECT COUNT(*)
            FROM attendances a
            LEFT JOIN time_slots ts ON ts.id = a.time_slot_id
            WHERE a.time_slot_id IS NOT NULL AND ts.id IS NULL
        ");

        $orphans['attendance_time_slot'] = [
            'name' => 'Attendance records with invalid time_slot_id',
            'count' => (int) $orphanAttendance,
            'severity' => $orphanAttendance > 0 ? 'error' : 'passed',
            'description' => 'Attendance records referencing non-existent time slots',
            'fix' => 'Consider setting time_slot_id to NULL or restoring time_slot records',
        ];

        $this->logCheckResult($orphans['attendance_time_slot']);

        // Check 2: Substitution records with invalid timetable_id
        $orphanSubstitution = DB::scalar("
            SELECT COUNT(*)
            FROM substitutions s
            LEFT JOIN timetables t ON t.id = s.timetable_id
            WHERE s.timetable_id IS NOT NULL AND t.id IS NULL
        ");

        $orphans['substitution_timetable'] = [
            'name' => 'Substitution records with invalid timetable_id',
            'count' => (int) $orphanSubstitution,
            'severity' => $orphanSubstitution > 0 ? 'error' : 'passed',
            'description' => 'Substitution records referencing non-existent timetables',
            'fix' => 'Delete orphan substitution records or restore timetables',
        ];

        $this->logCheckResult($orphans['substitution_timetable']);

        // Check 3: Timetable entries with invalid template_id
        $orphanTimetableTemplate = DB::scalar("
            SELECT COUNT(*)
            FROM timetables t
            LEFT JOIN timetable_templates tt ON tt.id = t.timetable_template_id
            WHERE t.timetable_template_id IS NOT NULL AND tt.id IS NULL
        ");

        $orphans['timetable_template'] = [
            'name' => 'Timetable entries with invalid timetable_template_id',
            'count' => (int) $orphanTimetableTemplate,
            'severity' => $orphanTimetableTemplate > 0 ? 'warning' : 'passed',
            'description' => 'Timetable entries referencing non-existent templates',
            'fix' => 'Set template_id to NULL or restore template records',
        ];

        $this->logCheckResult($orphans['timetable_template']);

        // Check 4: Timetable entries with invalid time_slot_id
        $orphanTimetableTimeSlot = DB::scalar("
            SELECT COUNT(*)
            FROM timetables t
            LEFT JOIN time_slots ts ON ts.id = t.time_slot_id
            WHERE t.time_slot_id IS NOT NULL AND ts.id IS NULL
        ");

        $orphans['timetable_time_slot'] = [
            'name' => 'Timetable entries with invalid time_slot_id',
            'count' => (int) $orphanTimetableTimeSlot,
            'severity' => $orphanTimetableTimeSlot > 0 ? 'warning' : 'passed',
            'description' => 'Timetable entries referencing non-existent time slots',
            'fix' => 'Set time_slot_id to NULL or restore time_slot records',
        ];

        $this->logCheckResult($orphans['timetable_time_slot']);

        // Check 5: Timetable entries with invalid class_section_id
        $orphanTimetableSection = DB::scalar("
            SELECT COUNT(*)
            FROM timetables t
            LEFT JOIN class_sections cs ON cs.id = t.class_section_id
            WHERE t.class_section_id IS NOT NULL AND cs.id IS NULL
        ");

        $orphans['timetable_section'] = [
            'name' => 'Timetable entries with invalid class_section_id',
            'count' => (int) $orphanTimetableSection,
            'severity' => $orphanTimetableSection > 0 ? 'error' : 'passed',
            'description' => 'Timetable entries referencing non-existent class sections',
            'fix' => 'Delete orphan timetable entries or restore class sections',
        ];

        $this->logCheckResult($orphans['timetable_section']);

        // Check 6: Timetable entries with invalid term_id
        $orphanTimetableTerm = DB::scalar("
            SELECT COUNT(*)
            FROM timetables t
            LEFT JOIN terms tm ON tm.id = t.term_id
            WHERE t.term_id IS NOT NULL AND tm.id IS NULL
        ");

        $orphans['timetable_term'] = [
            'name' => 'Timetable entries with invalid term_id',
            'count' => (int) $orphanTimetableTerm,
            'severity' => $orphanTimetableTerm > 0 ? 'error' : 'passed',
            'description' => 'Timetable entries referencing non-existent terms',
            'fix' => 'Delete orphan timetable entries or restore term records',
        ];

        $this->logCheckResult($orphans['timetable_term']);

        $this->results['checks']['orphans'] = $orphans;
        $this->newLine();

        // Phase 1.5: Show attendance-timetable mapping (replaces the View)
        $this->auditAttendanceTimetableMapping();
    }

    /**
     * Phase 1.5: Show attendance-timetable mapping status
     *
     * This replaces the CREATE VIEW statement that was removed from migration
     * for best practices (View is permanent, audit should be temporary).
     *
     * Shows the relationship between attendances and timetables.
     */
    private function auditAttendanceTimetableMapping(): void
    {
        $this->info('📋 Phase 1.5: Attendance-Timetable Mapping');
        $this->output->newLine();

        // Check if timetable_id column exists
        $hasTimetableId = DB::scalar("
            SELECT COUNT(*)
            FROM information_schema.columns
            WHERE table_name = 'attendances'
            AND column_name = 'timetable_id'
            AND table_schema = DATABASE()
        ") > 0;

        if (!$hasTimetableId) {
            $this->warn('⚠️  timetable_id column not found. Run migration first.');
            return;
        }

        // Run the mapping query (same as the View we removed)
        $mappingStats = DB::select("
            SELECT 
                COUNT(*) as total_attendances,
                SUM(CASE WHEN timetable_id IS NOT NULL THEN 1 ELSE 0 END) as linked,
                SUM(CASE WHEN timetable_id IS NULL THEN 1 ELSE 0 END) as unlinked
            FROM attendances
        ")[0];

        $total = (int) $mappingStats->total_attendances;
        $linked = (int) $mappingStats->linked;
        $unlinked = (int) $mappingStats->unlinked;

        $this->info("   📊 Attendance-Timetable Mapping Stats:");
        $this->info("      - Total attendance records: {$total}");
        $this->info("      - Linked to timetable: {$linked}");
        $this->info("      - Unlinked (need backfill): {$unlinked}");

        if ($total > 0) {
            $linkPercent = round(($linked / $total) * 100, 1);
            $this->info("      - Link percentage: {$linkPercent}%");
        }

        // Store in results
        $this->results['checks']['attendance_timetable_mapping'] = [
            'total_attendances' => $total,
            'linked' => $linked,
            'unlinked' => $unlinked,
            'linked_percentage' => $total > 0 ? round(($linked / $total) * 100, 1) : 0,
        ];

        $this->newLine();
    }

    /**
     * Audit Phase 2: Check for data integrity issues.
     */
    private function auditDataIntegrity(): void
    {
        $this->info('📋 Phase 2: Data Integrity Audit');
        $this->output->newLine();

        $integrity = [];

        // Check 1: Duplicate attendance records
        $duplicateAttendances = DB::scalar("
            SELECT COUNT(*)
            FROM (
                SELECT student_id, date, time_slot_id, COUNT(*) as cnt
                FROM attendances
                WHERE student_id IS NOT NULL 
                  AND date IS NOT NULL 
                  AND time_slot_id IS NOT NULL
                GROUP BY student_id, date, time_slot_id
                HAVING COUNT(*) > 1
            ) duplicates
        ");

        $integrity['duplicate_attendance'] = [
            'name' => 'Duplicate attendance records',
            'count' => (int) $duplicateAttendances,
            'severity' => $duplicateAttendances > 0 ? 'error' : 'passed',
            'description' => 'Multiple attendance records for same student/date/time_slot',
            'fix' => 'Run deduplication script to merge or delete duplicates',
        ];

        $this->logCheckResult($integrity['duplicate_attendance']);

        // Check 2: Attendance records with NULL in required fields
        $nullRequiredFields = DB::scalar("
            SELECT COUNT(*)
            FROM attendances
            WHERE student_id IS NULL 
               OR date IS NULL
               OR (time_slot_id IS NULL AND timetable_id IS NULL)
        ");

        $integrity['null_required'] = [
            'name' => 'Attendance records with NULL in required fields',
            'count' => (int) $nullRequiredFields,
            'severity' => $nullRequiredFields > 0 ? 'warning' : 'passed',
            'description' => 'Records missing student_id, date, or both time_slot_id and timetable_id',
            'fix' => 'Review and either populate missing data or soft-delete records',
        ];

        $this->logCheckResult($integrity['null_required']);

        // Check 3: Future dates in attendance (potential data entry errors)
        $futureDates = DB::scalar("
            SELECT COUNT(*)
            FROM attendances
            WHERE date > CURDATE()
        ");

        $integrity['future_dates'] = [
            'name' => 'Attendance records with future dates',
            'count' => (int) $futureDates,
            'severity' => $futureDates > 100 ? 'warning' : 'info',
            'description' => 'Records with dates in the future (potential data entry errors)',
            'fix' => 'Review and correct dates if they are data entry errors',
        ];

        $this->logCheckResult($integrity['future_dates']);

        // Check 4: Timetable entries with NULL in required fields
        $timetableNullFields = DB::scalar("
            SELECT COUNT(*)
            FROM timetables
            WHERE class_section_id IS NULL 
               OR term_id IS NULL
               OR (time_slot_id IS NULL AND timetable_template_id IS NULL)
        ");

        $integrity['timetable_null_fields'] = [
            'name' => 'Timetable entries with NULL in required fields',
            'count' => (int) $timetableNullFields,
            'severity' => $timetableNullFields > 0 ? 'warning' : 'passed',
            'description' => 'Records missing class_section_id, term_id, or both time_slot_id and template_id',
            'fix' => 'Review and either populate missing data or delete invalid entries',
        ];

        $this->logCheckResult($integrity['timetable_null_fields']);

        $this->results['checks']['integrity'] = $integrity;
        $this->newLine();
    }

    /**
     * Audit Phase 3: Check Phase 1 readiness.
     */
    private function auditPhase1Readiness(): void
    {
        $this->info('📋 Phase 3: Phase 1 Readiness Check');
        $this->output->newLine();

        $readiness = [];

        // Check 1: timetable_id column existence
        $hasTimetableIdColumn = DB::scalar("
            SELECT COUNT(*)
            FROM information_schema.columns
            WHERE table_name = 'attendances'
            AND column_name = 'timetable_id'
            AND table_schema = DATABASE()
        ") > 0;

        $readiness['has_timetable_id'] = [
            'name' => 'timetable_id column exists in attendances',
            'count' => $hasTimetableIdColumn ? 1 : 0,
            'severity' => $hasTimetableIdColumn ? 'passed' : 'info',
            'description' => 'Whether the timetable_id column has been added',
            'fix' => 'Run migration: php artisan migrate',
        ];

        $this->logCheckResult($readiness['has_timetable_id']);

        // Check 2: Estimate backfill scope
        if ($hasTimetableIdColumn) {
            $needsBackfill = DB::scalar("
                SELECT COUNT(*)
                FROM attendances
                WHERE timetable_id IS NULL AND time_slot_id IS NOT NULL
            ");

            $readiness['backfill_scope'] = [
                'name' => 'Records needing backfill',
                'count' => (int) $needsBackfill,
                'severity' => $needsBackfill > 10000 ? 'warning' : 'passed',
                'description' => "Number of records that need timetable_id backfilled: {$needsBackfill}",
                'fix' => 'Run: php artisan timetable:backfill-attendance-timetable-id',
            ];

            $this->logCheckResult($readiness['backfill_scope']);

            // Check 3: Foreign key constraint status
            $hasForeignKey = DB::scalar("
                SELECT COUNT(*)
                FROM information_schema.table_constraints tc
                JOIN information_schema.key_column_usage kcu
                    ON tc.constraint_name = kcu.constraint_name
                    AND tc.table_name = kcu.table_name
                WHERE tc.table_name = 'attendances'
                AND tc.constraint_type = 'FOREIGN KEY'
                AND kcu.column_name = 'timetable_id'
                AND tc.table_schema = DATABASE()
            ") > 0;

            $readiness['foreign_key'] = [
                'name' => 'Foreign key constraint on timetable_id',
                'count' => $hasForeignKey ? 1 : 0,
                'severity' => $hasForeignKey ? 'passed' : 'warning',
                'description' => 'Whether foreign key constraint exists',
                'fix' => 'Migration should have added this constraint',
            ];

            $this->logCheckResult($readiness['foreign_key']);
        }

        $this->results['checks']['readiness'] = $readiness;
        $this->newLine();
    }

    /**
     * Log a check result and update counters.
     */
    private function logCheckResult(array $check): void
    {
        $icon = $check['severity'] === 'passed' ? '✅' : 
                ($check['severity'] === 'error' ? '❌' : 
                ($check['severity'] === 'warning' ? '⚠️' : 'ℹ️'));

        $this->info("{$icon} {$check['name']}: {$check['count']}");

        if ($this->output->isVerbose() && $check['severity'] !== 'passed') {
            $this->comment("   Description: {$check['description']}");
            $this->comment("   Fix: {$check['fix']}");
        }

        // Update summary
        if ($check['severity'] === 'passed') {
            $this->results['summary']['passed']++;
        } elseif ($check['severity'] === 'warning') {
            $this->results['summary']['warnings']++;
        } else {
            $this->results['summary']['errors']++;
        }
    }

    /**
     * Display final audit results.
     */
    private function displayResults(): void
    {
        $this->info('========================================');
        $this->info('📊 Audit Summary');
        $this->info('========================================');
        $this->newLine();

        $this->info("✅ Passed: {$this->results['summary']['passed']}");
        $this->warn("⚠️  Warnings: {$this->results['summary']['warnings']}");
        $this->error("❌ Errors: {$this->results['summary']['errors']}");
        $this->newLine();

        // Determine overall status
        if ($this->results['summary']['errors'] > 0) {
            $this->results['status'] = 'failed';
            $this->error('🚫 Audit FAILED - Fix errors before proceeding');
            $this->info('Run with --fix to apply safe fixes where available.');
        } elseif ($this->results['summary']['warnings'] > 0) {
            $this->results['status'] = 'passed_with_warnings';
            $this->warn('⚠️  Audit PASSED with warnings - Review before proceeding');
        } else {
            $this->results['status'] = 'passed';
            $this->info('✅ Audit PASSED - System is ready for next phase');
        }

        // Log results
        Log::info('Timetable data audit completed', $this->results);

        // Output JSON if requested
        if ($this->option('format') === 'json') {
            $this->newLine();
            $this->info(json_encode($this->results, JSON_PRETTY_PRINT));
        }
    }

    /**
     * Apply safe fixes where possible.
     */
    private function applySafeFixes(): void
    {
        $this->info('🔧 Applying Safe Fixes...');
        $this->newLine();

        // Fix 1: Set time_slot_id to NULL for orphan attendance records
        $orphanAttendance = DB::scalar("
            SELECT COUNT(*)
            FROM attendances a
            LEFT JOIN time_slots ts ON ts.id = a.time_slot_id
            WHERE a.time_slot_id IS NOT NULL AND ts.id IS NULL
        ");

        if ($orphanAttendance > 0) {
            $fixed = DB::update("
                UPDATE attendances a
                LEFT JOIN time_slots ts ON ts.id = a.time_slot_id
                SET a.time_slot_id = NULL
                WHERE a.time_slot_id IS NOT NULL AND ts.id IS NULL
            ");

            $this->info("✅ Fixed {$fixed} orphan attendance records (set time_slot_id to NULL)");
        }

        // Note: We don't auto-fix more serious issues without manual review
        $this->info('ℹ️  Safe fixes applied. Manual review needed for other issues.');
    }
}

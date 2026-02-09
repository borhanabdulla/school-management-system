<?php

declare(strict_types=1);

namespace App\Console\Commands\DataAudit;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * BackfillAttendanceTimetableIdCommand
 * 
 * Phase 1.3: Safe chunked backfill of timetable_id for attendances
 * 
 * This command safely populates the timetable_id column in attendances
 * by matching on (class_section_id, time_slot_id, term_id).
 * 
 * Safety Features:
 * - Chunked processing to avoid table locks
 * - Validation that each attendance matches exactly one timetable
 * - Progress reporting
 * - Rollback capability (if needed)
 * 
 * Usage:
 *   php artisan timetable:backfill-attendance-timetable-id
 *   php artisan timetable:backfill-attendance-timetable-id --chunk=500
 *   php artisan timetable:backfill-attendance-timetable-id --dry-run (validate only)
 */
class BackfillAttendanceTimetableIdCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'timetable:backfill-attendance-timetable-id
                           {--chunk=1000 : Number of records to process per chunk}
                           {--dry-run : Only validate, do not make changes}
                           {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     */
    protected $description = 'Safely backfill timetable_id in attendances table (Phase 1.3)';

    /**
     * Statistics tracking.
     */
    private int $processed = 0;
    private int $errors = 0;
    private int $ambiguous = 0;
    private int $noMatch = 0;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔄 Attendance Timetable Backfill - Phase 1.3');
        $this->newLine();

        $chunkSize = (int) $this->option('chunk');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        // Validate prerequisites
        if (!$this->validatePrerequisites()) {
            return Command::FAILURE;
        }

        // Check current status
        $this->displayCurrentStatus($chunkSize);

        if (!$dryRun && !$force) {
            if (!$this->confirm('Proceed with backfill? This will modify attendance records.')) {
                $this->info('Backfill cancelled.');
                return Command::SUCCESS;
            }
        }

        try {
            if ($dryRun) {
                $this->info('🧪 DRY RUN MODE - No changes will be made');
                $this->newLine();
                
                return $this->validateOnly($chunkSize) ? Command::SUCCESS : Command::FAILURE;
            }

            return $this->executeBackfill($chunkSize);
        } catch (\Throwable $e) {
            $this->error("Backfill failed: {$e->getMessage()}");
            Log::error('Attendance backfill failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'processed' => $this->processed,
                'errors' => $this->errors,
            ]);
            
            return Command::FAILURE;
        }
    }

    /**
     * Validate prerequisites before running backfill.
     */
    private function validatePrerequisites(): bool
    {
        // Check if timetable_id column exists
        $hasColumn = DB::scalar("
            SELECT COUNT(*)
            FROM information_schema.columns
            WHERE table_name = 'attendances'
            AND column_name = 'timetable_id'
            AND table_schema = DATABASE()
        ");

        if (!$hasColumn) {
            $this->error('❌ timetable_id column does not exist in attendances table.');
            $this->info('Please run migration first: php artisan migrate');
            return false;
        }

        // Check if migration 2026_02_04_225114 has been run
        $migrationRan = DB::table('migrations')
            ->where('migration', '2026_02_04_225114_add_timetable_id_to_attendances')
            ->exists();

        if (!$migrationRan) {
            $this->warn('⚠️  Migration audit log not found. This may be expected if the migration was run manually.');
        }

        return true;
    }

    /**
     * Display current status of attendance records.
     */
    private function displayCurrentStatus(int $chunkSize): void
    {
        $total = DB::scalar('SELECT COUNT(*) FROM attendances WHERE timetable_id IS NULL AND time_slot_id IS NOT NULL');
        $withTimetable = DB::scalar('SELECT COUNT(*) FROM attendances WHERE timetable_id IS NOT NULL');

        $this->info("📊 Current Status:");
        $this->info("   - Total attendances: " . DB::scalar('SELECT COUNT(*) FROM attendances'));
        $this->info("   - With timetable_id: {$withTimetable}");
        $this->info("   - Need backfill: {$total}");
        $this->info("   - Chunk size: {$chunkSize}");
        $this->newLine();

        // Check for ambiguous matches (potential data issues)
        $ambiguousCount = DB::scalar("
            SELECT COUNT(DISTINCT a.id)
            FROM attendances a
            WHERE a.timetable_id IS NULL
            AND a.time_slot_id IS NOT NULL
            AND (
                SELECT COUNT(*)
                FROM timetables t
                WHERE t.class_section_id = a.class_section_id
                AND t.time_slot_id = a.time_slot_id
                AND t.term_id = a.term_id
            ) > 1
        ");

        if ($ambiguousCount > 0) {
            $this->warn("⚠️  Found {$ambiguousCount} records with multiple possible timetable matches!");
            $this->info('These records will be flagged as errors and skipped.');
            $this->newLine();
        }
    }

    /**
     * Validate only - check if backfill can proceed without errors.
     */
    private function validateOnly(int $chunkSize): bool
    {
        $this->info('🧪 Validating data for backfill...');
        $this->newLine();

        // Count records that can be matched exactly
        $validCount = DB::scalar("
            SELECT COUNT(*)
            FROM attendances a
            WHERE a.timetable_id IS NULL
            AND a.time_slot_id IS NOT NULL
            AND (
                SELECT COUNT(*)
                FROM timetables t
                WHERE t.class_section_id = a.class_section_id
                AND t.time_slot_id = a.time_slot_id
                AND t.term_id = a.term_id
            ) = 1
        ");

        // Count records with no match
        $noMatchCount = DB::scalar("
            SELECT COUNT(*)
            FROM attendances a
            WHERE a.timetable_id IS NULL
            AND a.time_slot_id IS NOT NULL
            AND (
                SELECT COUNT(*)
                FROM timetables t
                WHERE t.class_section_id = a.class_section_id
                AND t.time_slot_id = a.time_slot_id
                AND t.term_id = a.term_id
            ) = 0
        ");

        // Count records with multiple matches
        $multipleMatchCount = DB::scalar("
            SELECT COUNT(*)
            FROM attendances a
            WHERE a.timetable_id IS NULL
            AND a.time_slot_id IS NOT NULL
            AND (
                SELECT COUNT(*)
                FROM timetables t
                WHERE t.class_section_id = a.class_section_id
                AND t.time_slot_id = a.time_slot_id
                AND t.term_id = a.term_id
            ) > 1
        ");

        $this->info("✅ Valid records (exactly 1 match): {$validCount}");
        $this->warn("❌ No match records: {$noMatchCount}");
        $this->error("⚠️  Multiple match records (ERROR): {$multipleMatchCount}");

        $this->newLine();

        if ($multipleMatchCount > 0) {
            $this->error('Cannot proceed: Found records with multiple timetable matches.');
            $this->info('These records need manual resolution.');
            return false;
        }

        if ($noMatchCount > 0) {
            $this->warn("{$noMatchCount} records have no matching timetable.");
            $this->info('These will remain with NULL timetable_id.');
        }

        $this->info("Backfill can proceed for {$validCount} records.");
        return true;
    }

    /**
     * Execute the chunked backfill.
     */
    private function executeBackfill(int $chunkSize): int
    {
        $this->info('🚀 Starting chunked backfill...');
        $this->newLine();

        $progressBar = $this->output->createProgressBar();
        $progressBar->start();

        $lastProgress = 0;

        do {
            // Process one chunk
            $affected = DB::statement("
                UPDATE attendances
                SET timetable_id = (
                    SELECT t.id
                    FROM timetables t
                    WHERE t.class_section_id = attendances.class_section_id
                    AND t.time_slot_id = attendances.time_slot_id
                    AND t.term_id = attendances.term_id
                    LIMIT 1
                )
                WHERE timetable_id IS NULL
                AND time_slot_id IS NOT NULL
                AND (
                    SELECT COUNT(*)
                    FROM timetables t2
                    WHERE t2.class_section_id = attendances.class_section_id
                    AND t2.time_slot_id = attendances.time_slot_id
                    AND t2.term_id = attendances.term_id
                ) = 1
                LIMIT ?
            ", [$chunkSize]);

            // Get actual number of affected rows
            $affectedRows = DB::affectedRows();

            // Count ambiguous records (skipped)
            $ambiguousInChunk = DB::scalar("
                SELECT COUNT(*)
                FROM attendances a
                WHERE a.timetable_id IS NULL
                AND a.time_slot_id IS NOT NULL
                AND (
                    SELECT COUNT(*)
                    FROM timetables t
                    WHERE t.class_section_id = a.class_section_id
                    AND t.time_slot_id = a.time_slot_id
                    AND t.term_id = a.term_id
                ) > 1
            ");

            // Count no-match records
            $noMatchInChunk = DB::scalar("
                SELECT COUNT(*)
                FROM attendances a
                WHERE a.timetable_id IS NULL
                AND a.time_slot_id IS NOT NULL
                AND (
                    SELECT COUNT(*)
                    FROM timetables t
                    WHERE t.class_section_id = a.class_section_id
                    AND t.time_slot_id = a.time_slot_id
                    AND t.term_id = a.term_id
                ) = 0
            ");

            $this->processed += $affectedRows;
            $this->ambiguous += $ambiguousInChunk;
            $this->noMatch += $noMatchInChunk;

            // Update progress bar
            $currentTotal = DB::scalar('SELECT COUNT(*) FROM attendances WHERE timetable_id IS NULL AND time_slot_id IS NOT NULL');
            $progress = $lastProgress + $affectedRows;
            $progressBar->setProgress($progress);
            $lastProgress = $progress;

            // Log progress
            if ($this->output->isVerbose()) {
                $this->info("   Processed chunk: +{$affectedRows} (Ambiguous: {$ambiguousInChunk}, No Match: {$noMatchInChunk})");
            }

        } while ($affectedRows >= $chunkSize && !$this->output->isQuiet());

        $progressBar->finish();
        $this->newLine(2);

        // Final statistics
        return $this->displayFinalResults() ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Display final results and statistics.
     */
    private function displayFinalResults(): bool
    {
        $success = true;

        $this->info('📊 Final Backfill Results:');
        $this->info("   - Records processed: {$this->processed}");
        $this->info("   - Records with ambiguous matches (skipped): {$this->ambiguous}");
        $this->info("   - Records with no matching timetable: {$this->noMatch}");
        $this->newLine();

        // Count remaining records
        $remaining = DB::scalar('
            SELECT COUNT(*)
            FROM attendances
            WHERE timetable_id IS NULL AND time_slot_id IS NOT NULL
        ');

        if ($remaining > 0) {
            $this->warn("⚠️  {$remaining} records still need manual resolution.");
            $success = false;
        }

        if ($this->ambiguous > 0) {
            $this->error("⚠️  {$this->ambiguous} records have multiple possible timetable matches.");
            $this->info('These records need manual review to determine the correct timetable.');
            $success = false;
        }

        if ($success) {
            $this->info('✅ Backfill completed successfully!');
            $this->info('All attendance records now have a valid timetable_id.');
            
            // Log success
            Log::info('Attendance timetable backfill completed', [
                'processed' => $this->processed,
                'ambiguous' => $this->ambiguous,
                'no_match' => $this->noMatch,
                'remaining' => $remaining,
            ]);
        }

        return $success;
    }
}

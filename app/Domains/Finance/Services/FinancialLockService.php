<?php

namespace App\Domains\Finance\Services;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use Illuminate\Support\Facades\DB;
use App\Domains\Shared\Models\User;
use Exception;

class FinancialLockService
{
    public function __construct(
        private \App\Domains\Finance\Services\YearEndReportService $reportService
    ) {
    }

    /**
     * Check if the academic year is financially closed.
     */
    public function isLocked(int $academicYearId): bool
    {
        return AcademicYear::where('id', $academicYearId)
            ->where('financial_status', 'closed')
            ->exists();
    }

    /**
     * Lock the academic year financially.
     */
    public function lock(AcademicYear $year, User $user, ?string $reason = null): void
    {
        // Idempotency: Return early if already closed
        if ($year->financial_status === 'closed') {
            return;
        }

        DB::transaction(function () use ($year, $user, $reason) {
            // Double check lock status inside transaction
            $year->refresh();
            if ($year->financial_status === 'closed') {
                return;
            }

            // 1. Generate Report Snapshots
            $snapshot = [
                'summary' => $this->reportService->yearSummary($year->id),
                'outstanding_by_payer' => $this->reportService->outstandingByPayer($year->id),
                'blocked_students' => $this->reportService->blockedStudents($year->id),
                'generated_at' => now()->toIso8601String(),
            ];

            // 2. Hash and Version
            $version = 'v1';
            $jsonSnapshot = json_encode($snapshot);
            $hash = hash('sha256', $jsonSnapshot);

            // 3. Create Audit Log
            \App\Domains\Finance\Models\FinancialClosingLog::create([
                'academic_year_id' => $year->id,
                'closed_by' => $user->id,
                'closed_at' => now(),
                'reason' => $reason,
                'report_snapshot' => $snapshot,
                'report_version' => $version,
                'snapshot_hash' => $hash,
            ]);

            // 4. Update Year Status
            $year->update([
                'financial_status' => 'closed',
                'financial_closed_at' => now(),
                'financial_closed_by' => $user->id,
            ]);
        });
    }

    /**
     * Unlock for corrections (requires high privilege - separate concern, but defining basic capability here)
     */
    public function unlock(AcademicYear $year, User $user, string $reason): void
    {
        DB::transaction(function () use ($year, $user, $reason) {
            $year->update([
                'financial_status' => 'open',
                'financial_closed_at' => null,
                'financial_closed_by' => null,
            ]);
            // Future: Add unlock log to FinancialClosingLogs or general audit
        });
    }

    /**
     * Ensure the year is open, or throw exception.
     */
    public function ensureOpen(int $academicYearId): void
    {
        if ($this->isLocked($academicYearId)) {
            throw new \RuntimeException('The academic year is financially closed. Modifications to financial rules or structure are not allowed.');
        }
    }
}

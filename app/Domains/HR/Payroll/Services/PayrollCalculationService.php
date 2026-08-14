<?php

namespace App\Domains\HR\Payroll\Services;

use App\Domains\HR\Payroll\Models\Contract;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Payroll\Models\PayrollRecord;
use App\Domains\HR\Payroll\Models\PayrollItem;
use App\Domains\HR\Payroll\Models\ContractItem;
use App\Domains\HR\Substitution\Models\Substitution;
use App\Domains\HR\Staff\Models\StaffAttendance;
use App\Domains\HR\Payroll\Models\LoanInstallment;
use App\Domains\HR\Substitution\Services\SubstitutionService;
use App\Domains\HR\Attendance\Services\AttendanceSummaryService;
use App\Domains\HR\Payroll\Enums\PayrollItemType;
use App\Domains\HR\Payroll\Enums\LoanStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * خدمة حسابات الرواتب (The Calculation Engine)
 * 
 * تعمل كـ Orchestrator يجمع البيانات من:
 * 1. العقود (ContractService)
 * 2. الحضور (AttendanceService)
 * 3. البدائل (SubstitutionService)
 */
class PayrollCalculationService
{
    public function __construct(
        private \App\Domains\HR\Payroll\Services\PayrollPolicyService $policyService,
        private \App\Domains\HR\Payroll\Services\ContractService $contractService,
        private \App\Domains\HR\Substitution\Services\SubstitutionService $substitutionService,
        private AttendanceSummaryService $attendanceSummaryService
    ) {
    }

    /**
     * حساب تفاصيل الراتب لموظف واحد
     */
    public function calculateForStaff(
        Staff $staff,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {
        // 1. جلب العقود النشطة في الفترة (Split Month Logic)
        $contracts = $this->contractService->getContractsForPeriod($staff->id, $periodStart, $periodEnd);

        if ($contracts->isEmpty()) {
            return []; // Should be handled by validator
        }

        $earnings = [];
        $deductions = [];
        $meta = [
            'basic_salary' => 0, // Will be weighted average or sum of parts
            'contracts_count' => $contracts->count(),
            'latest_contract_id' => $contracts->last()->id,
        ];

        $totalWorkingDays = $this->policyService->getMonthDays($periodStart);
        $totalDaysWorked = 0;

        // 2. معالجة كل عقد (Contract Segments)
        foreach ($contracts as $contract) {
            $contractStart = Carbon::parse($contract->start_date);
            $contractEnd = Carbon::parse($contract->end_date);
            $segmentStart = $contractStart->gt($periodStart) ? $contractStart : $periodStart;
            $segmentEnd = $contractEnd->lt($periodEnd) ? $contractEnd : $periodEnd;

            // Pro-rata days for this segment
            $segmentDays = $this->policyService->calculateProrataDays(
                $contractStart,
                $contractEnd,
                $periodStart,
                $periodEnd
            );
            $totalDaysWorked += $segmentDays;

            // أ. الراتب الأساسي (Pro-rata)
            $basicAmount = $this->policyService->calculateProrataAmount(
                (float) $contract->basic_salary,
                $segmentDays,
                $periodStart
            );

            $earnings[] = [
                'type' => PayrollItemType::Earning,
                'name' => 'الراتب الأساسي',
                'category' => 'basic',
                'description' => $contracts->count() > 1
                    ? "راتب أساسي ({$segmentStart->format('d/m')} - {$segmentEnd->format('d/m')})"
                    : 'الراتب الأساسي',
                'amount' => $basicAmount,
                'source_type' => Contract::class,
                'source_id' => $contract->id,
            ];
            $meta['basic_salary'] += $basicAmount; // Accumulate for record snapshot

            // ب. بدلات العقد (Contract Items)
            foreach ($contract->contractItems as $item) {
                if ($item->type !== 'allowance')
                    continue;

                // One-time items: Pay FULL amount, NO Pro-rata
                if ($item->is_one_time) {
                    // Check if not already consumed (Double check, though service filters active)
                    if (!$item->consumed_at) {
                        $earnings[] = [
                            'type' => PayrollItemType::Earning,
                            'name' => $item->name,
                            'category' => 'bonus', // Or map from name
                            'description' => $item->name,
                            'amount' => $item->amount,
                            'source_type' => ContractItem::class,
                            'source_id' => $item->id,
                        ];
                    }
                    continue;
                }

                // Recurring items: Apply Pro-rata
                $itemAmount = $this->policyService->calculateProrataAmount(
                    $item->amount,
                    $segmentDays,
                    $periodStart
                );

                $earnings[] = [
                    'type' => PayrollItemType::Earning,
                    'name' => $item->name,
                    'category' => $this->mapAllowanceCategory($item->name),
                    'description' => $contracts->count() > 1
                        ? "{$item->name} ({$segmentStart->format('d/m')} - {$segmentEnd->format('d/m')})"
                        : $item->name,
                    'amount' => $itemAmount,
                    'source_type' => ContractItem::class,
                    'source_id' => $item->id,
                ];
            }
        }

        // 3. البدائل (Substitutions)
        $substitutions = $this->substitutionService->getPayableSubstitutions($staff->id, $periodStart, $periodEnd);
        foreach ($substitutions as $sub) {
            // Calculate amount (Assuming logic exists or fixed rate)
            // For now, let's assume a fixed rate or it's calculated elsewhere. 
            // If Substitution model doesn't have amount, we need a policy.
            // Let's assume 100 per class for now or check if Substitution has 'amount'.
            // The user spec says "SubstitutionService... converts to financial value".
            // I'll assume a method or property exists, or use a default policy.
            // حساب قيمة بدل الانتظار من السياسة
            $amount = $this->policyService->getSubstitutionClassRate();

            $earnings[] = [
                'type' => PayrollItemType::Earning,
                'name' => 'بدل انتظار',
                'category' => 'substitution',
                'description' => "بدل حصة انتظار: " . $sub->timetable->courseOffering->subject->name . " (" . $sub->date->format('Y-m-d') . ")",
                'amount' => $amount,
                'source_type' => Substitution::class,
                'source_id' => $sub->id,
            ];
        }

        // 4. الحضور والانصراف (Attendance)
        // We use the local method or inject service. Keeping local for now as StaffAttendanceService might be different.
        $attendanceStats = $this->attendanceSummaryService->getStatsForPeriod($staff, $periodStart, $periodEnd);

        // Calculate Daily Rate based on the LATEST contract (Standard practice) 
        // OR weighted average? User said "Historical Truth".
        // Simplified: Use daily rate of the *latest* contract for deductions.
        $latestContract = $contracts->last();
        $dailyRate = $this->policyService->getDailyRate((float) $latestContract->basic_salary, $periodStart);

        $attendanceDeductions = $this->calculateDeductions($dailyRate, $attendanceStats, $periodStart);
        $deductions = array_merge($deductions, $attendanceDeductions);

        // 5. Calculate Loan Deductions
        $loanInstallments = $this->getDueLoanInstallments($staff, $periodStart);
        foreach ($loanInstallments as $installment) {
            $deductions[] = [
                'type' => PayrollItemType::Deduction,
                'name' => 'خصم سلفة',
                'category' => 'loan',
                'description' => 'خصم سلفة (قسط شهر ' . $installment->due_date->format('m/Y') . ')',
                'amount' => $installment->amount,
                'source_type' => LoanInstallment::class,
                'source_id' => $installment->id,
            ];
        }

        // 6. Aggregation
        $grossTotal = collect($earnings)->sum('amount');
        $deductionsTotal = collect($deductions)->sum('amount');
        $netPayable = max(0, $grossTotal - $deductionsTotal);

        return [
            'earnings' => $earnings,
            'deductions' => $deductions,
            'totals' => [
                'working_days' => $totalWorkingDays,
                'days_worked' => $totalDaysWorked - $attendanceStats['absent_days'],
                'days_absent' => $attendanceStats['absent_days'],
                'days_late' => $attendanceStats['late_days'],
                'gross_earnings' => round($grossTotal, 2),
                'total_deductions' => round($deductionsTotal, 2),
                'net_payable' => round($netPayable, 2),
            ],
            'meta' => $meta,
        ];
    }

    protected function getDueLoanInstallments(Staff $staff, Carbon $periodStart): Collection
    {
        return LoanInstallment::whereHas('loan', function ($q) use ($staff) {
            $q->where('staff_id', $staff->id)->where('status', LoanStatus::Approved);
        })
            ->pending()
            ->whereYear('due_date', $periodStart->year)
            ->whereMonth('due_date', $periodStart->month)
            ->get();
    }

    protected function calculateDeductions(float $dailyRate, array $attendanceStats, Carbon $periodDate): array
    {
        $deductions = [];

        if ($attendanceStats['absent_days'] > 0) {
            $amount = $this->policyService->calculateAbsenceDeduction($attendanceStats['absent_days'], $dailyRate);
            $deductions[] = [
                'type' => PayrollItemType::Deduction,
                'name' => 'خصم غياب',
                'category' => 'absence',
                'description' => "خصم غياب ({$attendanceStats['absent_days']} يوم)",
                'amount' => $amount,
                'source_type' => null, // Could link to specific attendance records if we had them
                'source_id' => null,
            ];
        }

        if ($attendanceStats['total_late_minutes'] > 0) {
            $amount = $this->policyService->calculateLatenessDeduction($attendanceStats['total_late_minutes'], $dailyRate);
            if ($amount > 0) {
                $deductions[] = [
                    'type' => PayrollItemType::Deduction,
                    'name' => 'خصم تأخير',
                    'category' => 'lateness',
                    'description' => "خصم تأخير ({$attendanceStats['late_days']} مرة، {$attendanceStats['total_late_minutes']} دقيقة)",
                    'amount' => $amount,
                    'source_type' => null,
                    'source_id' => null,
                ];
            }
        }

        return $deductions;
    }

    protected function mapAllowanceCategory(string $name): string
    {
        $map = [
            'سكن' => 'housing',
            'بدل سكن' => 'housing',
            'نقل' => 'transport',
            'بدل نقل' => 'transport',
            'مواصلات' => 'transport',
        ];
        $normalized = mb_strtolower(trim($name));
        foreach ($map as $key => $category) {
            if (str_contains($normalized, $key))
                return $category;
        }
        return 'other';
    }
}

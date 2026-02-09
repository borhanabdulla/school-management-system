<?php

namespace App\Domains\HR\Leave\Services;

use App\Domains\HR\Leave\Models\LeaveRequest;
use App\Domains\HR\Leave\Models\LeaveType;
use App\Domains\HR\Leave\Models\StaffLeaveBalance;
use App\Domains\HR\Leave\Enums\LeaveRequestStatus;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Staff\Enums\StaffAttendanceStatus;
use App\Domains\HR\Payroll\Exceptions\PeriodLockedException;
use App\Domains\HR\Payroll\Services\PayrollPeriodLockService;
use App\Domains\HR\Shared\Actions\CreateHrAmendmentAction;
use App\Domains\Academic\Calendar\Services\SchoolCalendarService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\ValidationException;

class LeaveService
{
    public function __construct(
        private SchoolCalendarService $calendar,
        private PayrollPeriodLockService $periodLockService,
        private CreateHrAmendmentAction $createAmendmentAction
    ) {
    }

    /**
     * Calculate actual leave days based on leave type configuration
     */
    public function calculateActualDays(string $startDate, string $endDate, $leaveTypeOrId): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($end->lt($start)) {
            return ['days' => 0, 'list' => []];
        }

        if ($leaveTypeOrId instanceof LeaveType) {
            $leaveType = $leaveTypeOrId;
        } else {
            $leaveType = LeaveType::find($leaveTypeOrId);
        }

        if (!$leaveType) {
            return ['days' => 0, 'list' => []];
        }

        $totalDays = 0;
        $daysList = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $isHoliday = $this->calendar->isHoliday($current);

            // If leave type excludes holidays and today is a holiday, skip counting
            if ($leaveType->excludes_holidays && $isHoliday) {
                $current->addDay();
                continue;
            }

            $totalDays++;
            $daysList[] = $current->format('Y-m-d');
            $current->addDay();
        }

        return ['days' => $totalDays, 'list' => $daysList];
    }

    public function submitRequest(array $data): LeaveRequest
    {
        return DB::transaction(function () use ($data) {
            $staffId = $data['staff_id'];
            $leaveTypeId = $data['leave_type_id'];
            $year = Carbon::parse($data['start_date'])->year;

            $calculation = $this->calculateActualDays($data['start_date'], $data['end_date'], $leaveTypeId);
            $daysCount = $calculation['days'];

            if ($daysCount === 0) {
                throw ValidationException::withMessages([
                    'start_date' => Lang::get('hr::leave.no_work_days_in_period'),
                ]);
            }

            // Check balance
            $leaveType = LeaveType::find($leaveTypeId);
            $balance = StaffLeaveBalance::firstOrCreate(
                ['staff_id' => $staffId, 'leave_type_id' => $leaveTypeId, 'year' => $year],
                ['remaining_days' => $leaveType->days_per_year]
            );

            if ($balance->remaining_days < $daysCount) {
                throw ValidationException::withMessages([
                    'leave_type_id' => Lang::get('hr::leave.insufficient_balance', [
                        'remaining' => $balance->remaining_days,
                        'requested' => $daysCount,
                    ]),
                ]);
            }

            // Prevent overlapping requests (pending/approved)
            $overlapExists = LeaveRequest::where('staff_id', $staffId)
                ->whereIn('status', [LeaveRequestStatus::Pending, LeaveRequestStatus::Approved])
                ->where(function ($q) use ($data) {
                    $q->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                        ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                        ->orWhere(function ($inner) use ($data) {
                            $inner->where('start_date', '<=', $data['start_date'])
                                ->where('end_date', '>=', $data['end_date']);
                        });
                })
                ->exists();

            if ($overlapExists) {
                throw ValidationException::withMessages([
                    'start_date' => Lang::get('hr::leave.overlap'),
                ]);
            }

            // Create request
            return LeaveRequest::create([
                'staff_id' => $staffId,
                'leave_type_id' => $leaveTypeId,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'days_count' => $daysCount,
                'reason' => $data['reason'],
                'attachment' => $data['attachment'] ?? null,
                'status' => LeaveRequestStatus::Pending,
            ]);
        });
    }

    public function approveRequest(LeaveRequest $request, int $approverId): void
    {
        if ($request->status !== LeaveRequestStatus::Pending) {
            return;
        }

        $lockStart = Carbon::parse($request->start_date);
        $lockEnd = Carbon::parse($request->end_date);
        $lockingBatch = $this->periodLockService->getLockingBatchForRange($lockStart, $lockEnd);
        if ($lockingBatch) {
            $this->createAmendmentAction->execute(
                amendable: $request,
                kind: 'leave',
                reason: $request->reason,
                payload: [
                    'period_start' => $lockingBatch->period_start->toDateString(),
                    'period_end' => $lockingBatch->period_end->toDateString(),
                    'status' => $lockingBatch->status->value,
                    'batch_id' => $lockingBatch->id,
                ],
                requestedBy: $approverId,
                staffId: $request->staff_id
            );

            throw new PeriodLockedException(
                $lockingBatch->period_start->toDateString(),
                $lockingBatch->period_end->toDateString(),
                $lockingBatch->status->value,
                $lockingBatch->id
            );
        }

        DB::transaction(function () use ($request, $approverId) {
            // Deduct balance
            /** @var Carbon $startDate */
            $startDate = $request->start_date;
            $year = $startDate->year;
            $balance = StaffLeaveBalance::where('staff_id', $request->staff_id)
                ->where('leave_type_id', $request->leave_type_id)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($balance && $balance->remaining_days >= $request->days_count) {
                $balance->decrement('remaining_days', $request->days_count);

                $request->update([
                    'status' => LeaveRequestStatus::Approved,
                    'approved_by' => $approverId,
                ]);

                // Update Attendance Records
                $this->updateAttendanceRecords($request, $approverId);

            } else {
                throw new \App\Domains\HR\Leave\Exceptions\InsufficientLeaveBalanceException(
                    $balance?->remaining_days ?? 0,
                    $request->days_count
                );
            }
        });
    }

    private function updateAttendanceRecords(LeaveRequest $request, int $approverId): void
    {
        // Use loaded leaveType if available, otherwise fallback to ID
        $leaveType = $request->relationLoaded('leaveType') ? $request->leaveType : $request->leave_type_id;

        $calculation = $this->calculateActualDays($request->start_date, $request->end_date, $leaveType);

        $records = [];
        $now = now();
        $leaveTypeName = $request->relationLoaded('leaveType') ? $request->leaveType->name : LeaveType::find($request->leave_type_id)->name;

        foreach ($calculation['list'] as $date) {
            $records[] = [
                'staff_id' => $request->staff_id,
                'date' => $date,
                'status' => StaffAttendanceStatus::Excused->value,
                'check_in' => null,
                'check_out' => null,
                'delay_minutes' => 0,
                'early_leave_minutes' => 0,
                'remarks' => Lang::get('hr::leave.auto_remarks', ['type' => $leaveTypeName]),
                'source' => 'auto',
                'recorded_by' => $approverId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach ($records as $record) {
            $existing = \App\Domains\HR\Staff\Models\StaffAttendance::where('staff_id', $record['staff_id'])
                ->where('date', $record['date'])
                ->first();

            if ($existing) {
                if ($existing->source !== 'auto') {
                    continue;
                }

                $existing->update([
                    'status' => $record['status'],
                    'check_in' => $record['check_in'],
                    'check_out' => $record['check_out'],
                    'delay_minutes' => $record['delay_minutes'],
                    'early_leave_minutes' => $record['early_leave_minutes'],
                    'remarks' => $record['remarks'],
                    'source' => $record['source'],
                    'recorded_by' => $record['recorded_by'],
                ]);

                continue;
            }

            \App\Domains\HR\Staff\Models\StaffAttendance::create($record);
        }
    }

    public function rejectRequest(LeaveRequest $request, int $approverId, ?string $reason): void
    {
        if ($request->status !== LeaveRequestStatus::Pending) {
            return;
        }

        $request->update([
            'status' => LeaveRequestStatus::Rejected,
            'approved_by' => $approverId,
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * إعادة احتساب الأرصدة وإصلاحها
     */
    public function recalculateBalances(?Staff $staff = null): array
    {
        $query = \App\Domains\HR\Staff\Models\Staff::query();
        if ($staff) {
            $query->where('id', $staff->id);
        }

        // Eager load balances to avoid N+1 on inner loop
        $staffMembers = $query->with('leaveBalances.leaveType')->get();
        $staffIds = $staffMembers->pluck('id')->toArray();

        // Bulk fetch used leaves for all relevant staff
        $allUsedLeaves = LeaveRequest::whereIn('staff_id', $staffIds)
            ->where('status', LeaveRequestStatus::Approved)
            ->select('staff_id', 'leave_type_id', DB::raw('SUM(days_count) as total_days'))
            ->groupBy('staff_id', 'leave_type_id')
            ->get()
            ->groupBy('staff_id');

        $fixedCount = 0;

        foreach ($staffMembers as $member) {
            // Get used leaves for this specific staff from the bulk collection
            $staffUsedLeaves = $allUsedLeaves->get($member->id);
            $usedLeavesMap = $staffUsedLeaves ? $staffUsedLeaves->pluck('total_days', 'leave_type_id') : collect();

            // Use the eager loaded balances
            foreach ($member->leaveBalances as $balance) {
                $actualUsed = $usedLeavesMap[$balance->leave_type_id] ?? 0;
                $totalDays = $balance->leaveType?->days_per_year ?? 0;
                $calculatedRemaining = $totalDays - $actualUsed;

                if ($balance->remaining_days != $calculatedRemaining) {
                    $balance->update([
                        'remaining_days' => $calculatedRemaining,
                    ]);
                    $fixedCount++;
                }
            }
        }

        return ['processed' => $staffMembers->count(), 'fixed' => $fixedCount];
    }
}

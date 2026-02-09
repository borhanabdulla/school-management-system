<?php

namespace App\Domains\HR\Attendance\Services;

use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Staff\Models\StaffAttendance;
use App\Domains\HR\Staff\Enums\StaffAttendanceStatus;
use App\Domains\HR\WorkShift\Models\WorkShift;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Calendar\Models\SchoolEvent;
use App\Domains\Academic\Calendar\Services\SchoolCalendarService;
use App\Domains\HR\Shared\Services\AuditLogService;
use App\Domains\HR\Attendance\Services\Calculators\AttendanceCalculator;
use App\Domains\HR\Payroll\Exceptions\PeriodLockedException;
use App\Domains\HR\Payroll\Services\PayrollPeriodLockService;
use App\Domains\HR\Shared\Actions\CreateHrAmendmentAction;
use App\Events\TeacherAbsentWithClasses;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceService
{
    public function __construct(
        private SchoolCalendarService $calendar,
        private AuditLogService $auditLog,
        private AttendanceCalculator $calculator,
        private PayrollPeriodLockService $periodLockService,
        private CreateHrAmendmentAction $createAmendmentAction
    ) {
    }

    /**
     * جلب ورقة الحضور لليوم
     * @return Collection|array - قائمة الموظفين مع سجلاتهم، أو معلومات العطلة
     */
    public function getAttendanceSheet(string $date, ?int $shiftId = null): Collection|array
    {
        $parsedDate = Carbon::parse($date);
        $dayName = strtolower(substr($parsedDate->format('l'), 0, 3)); // sun, mon, etc.

        // 1. فحص العطلة
        if ($this->calendar->isHoliday($date)) {
            $holidayName = $this->getHolidayName($date);

            // استثناء: الموظفون الذين يعملون في العطل
            $holidayWorkers = Staff::with(['workShift'])
                ->whereHas('workShift', fn($q) => $q->where('works_on_holidays', true))
                ->get();

            if ($holidayWorkers->isEmpty()) {
                return [
                    'is_holiday' => true,
                    'holiday_name' => $holidayName,
                    'staff' => collect([]),
                ];
            }

            // إرجاع العاملين في العطل فقط
            return $this->buildAttendanceSheet($holidayWorkers, $date);
        }

        // 2. جلب الموظفين حسب الوردية
        $query = Staff::with(['workShift', 'teacher'])
            ->whereHas('workShift', function ($q) use ($dayName) {
                $q->where('is_active', true)
                    ->whereJsonContains('working_days', $dayName);
            });

        if ($shiftId) {
            $query->where('work_shift_id', $shiftId);
        }

        $staff = $query->orderBy('first_name')->get();

        return $this->buildAttendanceSheet($staff, $date);
    }

    /**
     * بناء ورقة الحضور
     */
    private function buildAttendanceSheet(Collection $staff, string $date): Collection
    {
        // جلب السجلات الموجودة لهذا اليوم
        $existingRecords = StaffAttendance::where('date', $date)
            ->whereIn('staff_id', $staff->pluck('id'))
            ->get()
            ->keyBy('staff_id');

        return $staff->map(function ($employee) use ($existingRecords, $date) {
            $record = $existingRecords[$employee->id] ?? null;

            return [
                'staff_id' => $employee->id,
                'staff' => $employee,
                'shift' => $employee->workShift,
                'record' => $record,
                'check_in' => $record?->check_in,
                'check_out' => $record?->check_out,
                'status' => $record?->status ?? 'pending',
                'delay_minutes' => $record?->delay_minutes ?? 0,
                'is_saved' => $record !== null,
            ];
        });
    }

    /**
     * حفظ الحضور مع حساب التأخير تلقائياً
     */
    public function saveAttendance(int $staffId, string $date, ?string $checkIn, ?string $checkOut, int $recordedByUserId, ?string $remarks = null, string $source = 'manual'): StaffAttendance
    {
        $lockDate = Carbon::parse($date);
        $lockingBatch = $this->periodLockService->getLockingBatchForRange($lockDate, $lockDate);
        if ($lockingBatch) {
            throw new PeriodLockedException(
                $lockingBatch->period_start->toDateString(),
                $lockingBatch->period_end->toDateString(),
                $lockingBatch->status->value,
                $lockingBatch->id
            );
        }

        return DB::transaction(function () use ($staffId, $date, $checkIn, $checkOut, $recordedByUserId, $remarks, $source) {
            // البحث عن سجل موجود
            $attendance = StaffAttendance::firstOrNew([
                'staff_id' => $staffId,
                'date' => $date
            ]);

            // إعداد البيانات الجديدة
            $data = ['check_in' => $checkIn, 'check_out' => $checkOut, 'recorded_by' => $recordedByUserId, 'remarks' => $remarks, 'source' => $source,];

            // حساب الحالة والتأخير (Domain Logic)
            $this->calculateStatus($attendance, $checkIn, $checkOut);

            $attendance->fill($data);

            // التحقق من التغييرات الحقيقية (Dirty Checking)
            // نتجاهل updated_at ونركز على الحقول المؤثرة
            if ($attendance->isDirty(['status', 'check_in', 'check_out', 'remarks', 'delay_minutes', 'early_leave_minutes'])) {
                $isNew = !$attendance->exists;
                $oldValues = $isNew ? null : $attendance->getOriginal();

                $attendance->save();

                // تسجيل في Audit Log
                if ($isNew) {
                    $this->auditLog->logCreated($attendance);
                } else {
                    $this->auditLog->logUpdated($attendance, $oldValues);
                }

                // التحقق من غياب معلم
                $this->checkTeacherAbsence($attendance);
            }

            return $attendance;
        });
    }

    /**
     * منطق حساب الحالة (تم نقله من الموديل)
     */
    private function calculateStatus(StaffAttendance $attendance, ?string $checkIn, ?string $checkOut): void
    {
        // إذا لم يسجل حضور = غائب
        if (!$checkIn) {
            $attendance->status = StaffAttendanceStatus::Absent;
            $attendance->delay_minutes = 0;
            $attendance->early_leave_minutes = 0;
            return;
        }

        // جلب الوردية
        $staff = $attendance->staff ?? Staff::find($attendance->staff_id);
        $shift = $staff?->workShift;

        // إذا لم يكن له وردية (دوام جزئي/مقاول) = حاضر بدون حساب
        if (!$shift) {
            $attendance->status = StaffAttendanceStatus::Present;
            $attendance->delay_minutes = 0;
            $attendance->early_leave_minutes = 0;
            return;
        }

        // حساب التأخير باستخدام Domain Service
        $attendance->delay_minutes = $this->calculator->calculateDelay(
            $shift->start_time,
            $shift->grace_period_minutes,
            $checkIn
        );

        // حساب الخروج المبكر
        if ($checkOut) {
            $attendance->early_leave_minutes = $this->calculator->calculateEarlyLeave(
                $shift->end_time,
                $checkOut
            );
        } else {
            $attendance->early_leave_minutes = 0;
        }

        // تحديد الحالة
        if ($this->calculator->isLate($attendance->delay_minutes)) {
            $attendance->status = StaffAttendanceStatus::Late;
        } elseif ($attendance->early_leave_minutes > 0) {
            $attendance->status = StaffAttendanceStatus::Present; // يمكن تغييرها لاحقاً
        } else {
            $attendance->status = StaffAttendanceStatus::Present;
        }
    }

    /**
     * حفظ حضور متعدد (Bulk)
     */
    public function saveBulkAttendance(array $records, int $recordedByUserId): int
    {
        $saved = 0;

        DB::transaction(function () use ($records, $recordedByUserId, &$saved) {
            foreach ($records as $record) {
                $this->saveAttendance(
                    $record['staff_id'],
                    $record['date'],
                    $record['check_in'] ?? null,
                    $record['check_out'] ?? null,
                    $recordedByUserId,
                    $record['remarks'] ?? null
                );
                $saved++;
            }
        });

        return $saved;
    }

    /**
     * تصحيح سجل حضور (بأثر رجعي)
     */
    public function correctAttendance(int $attendanceId, array $corrections, int $correctedByUserId, string $reason): StaffAttendance
    {

        $attendance = StaffAttendance::findOrFail($attendanceId);
        $lockDate = Carbon::parse($attendance->date);
        $lockingBatch = $this->periodLockService->getLockingBatchForRange($lockDate, $lockDate);
        if ($lockingBatch) {
            $this->createAmendmentAction->execute(
                amendable: $attendance,
                kind: 'attendance',
                reason: $reason,
                payload: [
                    'corrections' => $corrections,
                    'period_start' => $lockingBatch->period_start->toDateString(),
                    'period_end' => $lockingBatch->period_end->toDateString(),
                    'status' => $lockingBatch->status->value,
                    'batch_id' => $lockingBatch->id,
                ],
                requestedBy: $correctedByUserId,
                staffId: $attendance->staff_id
            );

            throw new PeriodLockedException(
                $lockingBatch->period_start->toDateString(),
                $lockingBatch->period_end->toDateString(),
                $lockingBatch->status->value,
                $lockingBatch->id
            );
        }

        return DB::transaction(function () use ($attendance, $corrections, $correctedByUserId, $reason) {
            $attendance->fill($corrections);
            $attendance->source = 'correction';

            // إعادة الحساب بناءً على القيم الجديدة (أو الموجودة)
            $checkIn = $corrections['check_in'] ?? $attendance->check_in;
            $checkOut = $corrections['check_out'] ?? $attendance->check_out;

            $this->calculateStatus($attendance, $checkIn, $checkOut);

            if ($attendance->isDirty(['status', 'check_in', 'check_out', 'remarks', 'delay_minutes', 'early_leave_minutes'])) {
                $oldValues = $attendance->getOriginal();
                $attendance->save();

                // تسجيل التصحيح مع السبب
                $this->auditLog->logUpdated($attendance, $oldValues, $reason);
            }

            return $attendance;
        });
    }

    /**
     * FR-10: التحقق من تعارض غياب المعلم مع الجدول
     */
    private function checkTeacherAbsence(StaffAttendance $attendance): void
    {
        if ($attendance->status !== 'absent')
            return;

        $staff = $attendance->staff;
        if (!$staff->isTeacher())
            return;

        $teacherId = $staff->teacher->id;
        $dayOfWeek = strtolower(substr(Carbon::parse($attendance->date)->format('l'), 0, 3));

        // فحص وجود حصص للمعلم في هذا اليوم
        $hasClasses = Timetable::whereHas(
            'courseOffering',
            fn($q) =>
            $q->where('teacher_id', $teacherId)
        )->whereHas('timeSlot', fn($q) => $q->where('day_of_week', $dayOfWeek))->exists();

        if ($hasClasses) {
            // إطلاق حدث لإشعار النظام
            event(new TeacherAbsentWithClasses($staff->teacher, $attendance->date));
        }
    }

    /**
     * جلب اسم العطلة
     */
    private function getHolidayName(string $date): string
    {
        $activeYearId = school()->activeYearId();
        if (!$activeYearId) {
            return 'عطلة رسمية';
        }

        $event = SchoolEvent::query()
            ->where('academic_year_id', $activeYearId)
            ->holidays()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        return $event?->title ?? 'عطلة رسمية';
    }

    /**
     * جلب الورديات النشطة
     */
    public function getActiveShifts(): Collection
    {
        return WorkShift::active()->currentSeason()->orderBy('name')->get();
    }

    /**
     * إحصائيات الحضور اليومي
     */
    public function getDailyStats(string $date): array
    {
        $stats = StaffAttendance::where('date', $date)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused
            ")
            ->first();

        return [
            'total' => $stats->total ?? 0,
            'present' => $stats->present ?? 0,
            'late' => $stats->late ?? 0,
            'absent' => $stats->absent ?? 0,
            'excused' => $stats->excused ?? 0,
            'attendance_rate' => $stats->total > 0
                ? round((($stats->present + $stats->late) / $stats->total) * 100, 1)
                : 0,
        ];
    }
}

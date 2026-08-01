<?php

namespace App\Domains\Academic\Attendance\Actions;

use App\Domains\Academic\Attendance\Models\Attendance;
use App\Domains\Academic\Attendance\Events\StudentAttendanceSaved;
use App\Domains\Academic\Attendance\Enums\AttendanceStatus;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Services\AcademicWriteGuard;
use App\Domains\Shared\Models\User;
use App\Infrastructure\Exceptions\InvalidOperationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecordStudentAttendanceAction
{
    /**
     * حفظ البيانات (Bulk Upsert)
     * ✅ PR-5: Uses year-aware holiday check
     */
    public function execute(
        Timetable $timetable,
        string $date,
        array $studentsData,
        int $recordedByUserId,
        ?string $overrideReason = null
    ): void
    {
        // ✅ PR-5: Get academic year from timetable first
        $academicYearId = $timetable->classSection()->value('academic_year_id');
        if (!$academicYearId) {
            throw InvalidOperationException::make(__('attendance.cannot_save_without_academic_year'));
        }
        
        // 🛡️ PR-1: Guard against closed year
        app(AcademicWriteGuard::class)->assertYearNotClosed($academicYearId);
        
        // ✅ PR-5: Calendar Guard - Prevent recording attendance on holidays for the correct year
        $calendarService = app(\App\Domains\Academic\Calendar\Services\SchoolCalendarService::class);
        if ($calendarService->isHolidayForYear($date, $academicYearId)) {
            $user = User::find($recordedByUserId);
            $canOverride = $user?->can('attendance.override_holiday') || $user?->can('attendance.manage');
            $reason = is_string($overrideReason) ? trim($overrideReason) : '';

            if (!$canOverride) {
                throw InvalidOperationException::make(__('attendance.cannot_record_on_holiday', ['date' => $date]));
            }

            if ($reason === '') {
                throw InvalidOperationException::make(__('attendance.holiday_override_requires_reason'));
            }

            Log::notice('Attendance holiday override', [
                'date' => $date,
                'academic_year_id' => $academicYearId,
                'timetable_id' => $timetable->id,
                'recorded_by' => $recordedByUserId,
                'reason' => $reason,
            ]);
        }

        DB::transaction(function () use ($timetable, $date, $studentsData, $recordedByUserId, $academicYearId) {

            foreach ($studentsData as $data) {
                Attendance::updateOrCreate(
                    [
                        'student_id' => $data['student_id'],
                        'date' => $date,
                        'timetable_id' => $timetable->id, // ✅ PR-3: Use timetable_id as identity
                    ],
                    [
                        'time_slot_id' => $timetable->time_slot_id,
                        'class_section_id' => $timetable->class_section_id,
                        'academic_year_id' => $academicYearId,
                        'term_id' => $timetable->term_id,
                        'status' => $data['status'],
                        'delay_minutes' => ($data['status'] === AttendanceStatus::LATE->value) ? ((int) $data['delay_minutes']) : 0,
                        'remarks' => $data['remarks'] ?? null,
                        'recorded_by' => $recordedByUserId,
                    ]
                );
            }
        });

        // تحديد الطلاب الغائبين لإرسال الإشعارات
        $absentStudentIds = collect($studentsData)
            ->where('status', AttendanceStatus::ABSENT->value)
            ->pluck('student_id')
            ->toArray();

        // إطلاق الحدث إذا كان هناك غياب (للإشعارات)
        if (!empty($absentStudentIds)) {
            event(new StudentAttendanceSaved($timetable, $date, $absentStudentIds));
        }

        // إطلاق حدث الحفظ الجماعي (للمزامنة مع الدرجات)
        $allStudentIds = collect($studentsData)->pluck('student_id')->toArray();
        if (!empty($allStudentIds)) {
            event(new \App\Domains\Academic\Attendance\Events\AttendanceBatchSaved($timetable, $date, $allStudentIds));
        }
    }
}

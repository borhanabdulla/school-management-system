<?php

namespace App\Domains\Academic\Attendance\Services;

use App\Domains\Academic\Attendance\Models\Attendance;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Calendar\Services\SchoolCalendarService;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Domains\Academic\Attendance\Enums\AttendanceStatus;

class AttendanceReportService
{
    public function __construct(
        private SchoolCalendarService $calendarService
    ) {
    }

    /**
     * توليد تقرير الحضور الشهري (المصفوفة الذكية)
     * 
     * @return array ['students' => [], 'days' => [], 'matrix' => [], 'stats' => []]
     */
    public function getMonthlyReport(int $classSectionId, int $month, int $year, ?int $termId = null): array
    {
        if (!$termId) {
            throw new \InvalidArgumentException('termId is required for attendance reports.');
        }

        $term = Term::query()
            ->select('id', 'academic_year_id')
            ->find($termId);

        if (!$term) {
            throw new \InvalidArgumentException("Invalid termId: {$termId}.");
        }

        $academicYearId = (int) $term->academic_year_id;

        // 1. توليد أيام الشهر مع معلومات العطل
        $days = $this->getMonthDays($month, $year, $academicYearId);

        // 2. جلب طلاب الشعبة
        $students = app(\App\Domains\Academic\Student\Services\StudentLookupService::class)
            ->getByClassSection($classSectionId, $academicYearId);

        // 3. جلب جميع سجلات الحضور (حاضر، غائب، الخ) لتجنب N+1 والغاء الافتراضات
        $startDate = Carbon::create($year, $month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth();

        $attendanceRecords = Attendance::where('class_section_id', $classSectionId)
            ->where('academic_year_id', $academicYearId)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('term_id', $termId)
            ->get()
            ->groupBy(fn($record) => $record->student_id . '_' . $record->date->format('Y-m-d'));

        // 4. بناء المصفوفة
        $matrix = [];
        $stats = [];

        foreach ($students as $student) {
            $studentRow = [];
            $absenceCount = 0;
            $lateCount = 0;

            foreach ($days as $day) {
                $key = $student->id . '_' . $day['date'];

                if ($day['is_holiday']) {
                    // عطلة - لا حالة
                    $studentRow[$day['date']] = [
                        'status' => 'holiday',
                        'delay' => 0,
                    ];
                } elseif (isset($attendanceRecords[$key])) {
                    // ✅ يوجد سجل حقيقي (حاضر/غائب/متأخر)
                    $record = $attendanceRecords[$key]->first();
                    $studentRow[$day['date']] = [
                        'status' => $record->status,
                        'delay' => $record->delay_minutes ?? 0,
                    ];

                    if (in_array($record->status, [AttendanceStatus::ABSENT->value, AttendanceStatus::ESCAPED->value])) {
                        $absenceCount++;
                    } elseif ($record->status === AttendanceStatus::LATE->value) {
                        $lateCount++;
                    }
                } else {
                    // ✅ لا يوجد سجل => No Data (لا نفترض الحضور بعد الآن)
                    $studentRow[$day['date']] = [
                        'status' => 'no_data',
                        'delay' => 0,
                    ];
                }
            }

            $matrix[$student->id] = $studentRow;
            $stats[$student->id] = [
                'absences' => $absenceCount,
                'lates' => $lateCount,
            ];
        }

        return [
            'students' => $students,
            'days' => $days,
            'matrix' => $matrix,
            'stats' => $stats,
            'term_id' => $termId,
        ];
    }

    /**
     * توليد مصفوفة أيام الشهر مع معلومات العطل
     * ✅ PR1.1: Uses SchoolCalendarService as single source of truth (no duplicate logic)
     */
    public function getMonthDays(int $month, int $year, ?int $academicYearId = null): array
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $days = [];
        $period = CarbonPeriod::create($startDate, $endDate);
        $weekendDays = $academicYearId
            ? $this->calendarService->getWeekendDaysForYear($academicYearId)
            : $this->calendarService->getWeekendDays();

        foreach ($period as $date) {
            // ✅ PR1.1: Single source of truth - all calendar logic via SchoolCalendarService
            $isHoliday = $academicYearId
                ? $this->calendarService->isHolidayForYear($date, $academicYearId)
                : $this->calendarService->isHoliday($date);
            $isWeekend = in_array($date->dayOfWeek, $weekendDays, true);

            $days[] = [
                'date' => $date->format('Y-m-d'),
                'day_num' => $date->day,
                'day_name' => $date->locale('ar')->dayName,
                'day_short' => $date->locale('ar')->shortDayName,
                'is_holiday' => $isHoliday,
                'is_weekend' => $isWeekend,
            ];
        }

        return $days;
    }

    /**
     * حساب إجمالي أيام الدراسة الفعلية في الشهر
     */
    public function getSchoolDaysCount(int $month, int $year): int
    {
        $days = $this->getMonthDays($month, $year);
        return collect($days)->where('is_holiday', false)->count();
    }

    /**
     * حساب نسبة الحضور لطالب معين
     */
    public function calculateAttendanceRate(int $absences, int $totalSchoolDays): float
    {
        if ($totalSchoolDays === 0)
            return 100.0;

        $attendedDays = $totalSchoolDays - $absences;
        return round(($attendedDays / $totalSchoolDays) * 100, 1);
    }
}

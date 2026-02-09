<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Listeners;

use App\Domains\Academic\Attendance\Events\AttendanceBatchSaved;
use App\Domains\Academic\Grading\Models\GradebookMonth;
use App\Domains\Academic\Grading\Services\GradeSyncService;
use App\Domains\Academic\Student\Models\Student;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * SyncAttendanceToMonthlyGrade - مزامنة الحضور → درجات المواظبة
 * 
 * يعالج الفصل كاملاً في دفعات (Batch) لتحسين الأداء
 */
class SyncAttendanceToMonthlyGrade implements ShouldQueue
{
    public string $queue = 'grading';
    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(
        private GradeSyncService $syncService
    ) {
    }

    public function handle(AttendanceBatchSaved $event): void
    {
        $timetable = $event->timetable;
        // Ensure relation is loaded or access it (assuming relationship exists)
        $classSection = $timetable->classSection;
        $termId = $timetable->term_id;

        if (!$classSection) {
            Log::error('Attendance sync failed: ClassSection not found for timetable', ['timetable_id' => $timetable->id]);
            return;
        }

        if (!$termId) {
            Log::warning('Attendance sync skipped: Timetable has no term_id', [
                'timetable_id' => $timetable->id,
            ]);
            return;
        }

        // تحديد شهر الدفتر بناءً على التاريخ والسنة الدراسية
        $month = GradebookMonth::where('academic_year_id', $classSection->academic_year_id)
            ->where('term_id', $termId)
            ->where('start_date', '<=', $event->date)
            ->where('end_date', '>=', $event->date)
            ->first();

        if (!$month) {
            Log::warning('Attendance sync skipped: No GradebookMonth found for date', [
                'date' => $event->date,
                'academic_year_id' => $classSection->academic_year_id
            ]);
            return;
        }

        $studentIds = $event->studentIds;
        $batchSize = 50;

        // معالجة الطلاب
        foreach (array_chunk($studentIds, $batchSize) as $batchIds) {
            $students = Student::whereIn('id', $batchIds)->get();

            foreach ($students as $student) {
                try {
                    $this->syncService->syncAttendanceGrade(
                        student: $student,
                        classSection: $classSection,
                        month: $month
                    );
                } catch (\Exception $e) {
                    Log::warning('Attendance sync failed for student', [
                        'student_id' => $student->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        Log::info('Attendance batch sync completed', [
            'class_section_id' => $classSection->id,
            'students_count' => count($studentIds),
            'month' => $month->name
        ]);

        Cache::put('grading.queue.last_heartbeat_at', now()->toDateTimeString(), now()->addHours(6));
    }
}

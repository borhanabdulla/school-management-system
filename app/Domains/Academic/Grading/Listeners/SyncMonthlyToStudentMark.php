<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Listeners;

use App\Domains\Academic\Grading\Events\MonthlyGradeSaved;
use App\Domains\Academic\Grading\Actions\AggregateGradebookToTemplateMarksAction;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * SyncMonthlyToStudentMark - مستمع مزامنة الدرجات
 * 
 * يعمل في الخلفية (Queue) لمزامنة الدرجات الشهرية → StudentMark
 */
class SyncMonthlyToStudentMark implements ShouldQueue
{
    /**
     * اسم الـ Queue
     */
    public string $queue = 'grading';

    /**
     * ضمان تنفيذ الـ job بعد commit transaction فقط
     * 
     * هذا يمنع مشاكل race conditions حيث يُنفذ الـ job قبل حفظ البيانات في DB
     * 
     * @var bool
     */
    public bool $afterCommit = true;

    /**
     * عدد المحاولات قبل الفشل
     */
    public int $tries = 3;

    /**
     * التأخير بين المحاولات (بالثواني)
     */
    public array $backoff = [10, 30, 60];

    public function __construct(
        private AggregateGradebookToTemplateMarksAction $aggregateAction
    ) {
    }

    public function handle(MonthlyGradeSaved $event): void
    {
        try {
            $grade = $event->monthlyGrade;
            $offering = $grade->courseOffering ?? CourseOffering::find($grade->course_offering_id);
            if (!$offering) {
                Log::warning('Aggregate skipped: missing course offering', [
                    'monthly_grade_id' => $grade->id,
                    'course_offering_id' => $grade->course_offering_id,
                ]);
                return;
            }

            $this->aggregateAction->execute($offering, $grade->student_id);
            Cache::put('grading.queue.last_heartbeat_at', now()->toDateTimeString(), now()->addHours(6));
        } catch (\Exception $e) {
            Log::error('Grade aggregation exception', [
                'monthly_grade_id' => $event->monthlyGrade->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // إعادة المحاولة
        }
    }

    /**
     * معالجة الفشل النهائي
     */
    public function failed(MonthlyGradeSaved $event, \Throwable $exception): void
    {
        Log::critical('Grade sync permanently failed', [
            'monthly_grade_id' => $event->monthlyGrade->id,
            'error' => $exception->getMessage(),
        ]);
    }
}

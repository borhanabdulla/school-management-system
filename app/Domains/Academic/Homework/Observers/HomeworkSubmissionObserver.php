<?php

namespace App\Domains\Academic\Homework\Observers;

use App\Domains\Academic\Homework\Models\HomeworkSubmission;
use App\Domains\Academic\Homework\Enums\SubmissionStatus;
use App\Domains\Academic\Grading\Services\GradeSyncService;

/**
 * HomeworkSubmissionObserver - مراقب تسليمات الواجبات
 * 
 * يتعامل مع أحداث تحديث تسليمات الواجبات
 */
class HomeworkSubmissionObserver
{
    public function __construct(
        protected GradeSyncService $gradeSyncService
    ) {
    }

    /**
     * عند تحديث التسليم
     */
    public function updated(HomeworkSubmission $submission): void
    {
        // مزامنة الدرجة إذا تغيرت
        if ($submission->isDirty('score')) {
            $this->gradeSyncService->syncHomeworkGrade($submission);
        }

        // إرسال إشعار عند التقييم
        if (
            ($submission->isDirty('status') && $submission->status === SubmissionStatus::GRADED) ||
            ($submission->isDirty('score') && $submission->score !== null)
        ) {
            if ($submission->student && $submission->student->user) {
                $submission->student->user->notify(
                    new \App\Domains\Academic\Homework\Notifications\HomeworkGradedNotification($submission)
                );
            }
        }
    }
}

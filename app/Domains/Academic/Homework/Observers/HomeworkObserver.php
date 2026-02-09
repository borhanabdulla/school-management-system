<?php

namespace App\Domains\Academic\Homework\Observers;

use App\Domains\Academic\Homework\Enums\HomeworkStatus;
use App\Domains\Academic\Homework\Enums\SubmissionStatus;
use App\Domains\Academic\Homework\Models\Homework;
use App\Domains\Academic\Homework\Models\HomeworkSubmission;
use Illuminate\Support\Facades\Log;

/**
 * HomeworkObserver - مراقب الواجبات
 * 
 * يتعامل مع أحداث إنشاء وتحديث الواجبات
 */
class HomeworkObserver
{
    /**
     * عند إنشاء واجب جديد
     */
    public function created(Homework $homework): void
    {
        if ($homework->status === HomeworkStatus::PUBLISHED) {
            $this->distributeHomework($homework);
        }
    }

    /**
     * عند تحديث الواجب
     */
    public function updated(Homework $homework): void
    {
        if ($homework->isDirty('status') && $homework->status === HomeworkStatus::PUBLISHED) {
            $this->distributeHomework($homework);
        }
    }

    /**
     * توزيع الواجب على الطلاب
     */
    protected function distributeHomework(Homework $homework): void
    {
        $courseOffering = $homework->courseOffering;

        if (!$courseOffering) {
            return;
        }

        $students = $courseOffering->students;

        foreach ($students as $student) {
            HomeworkSubmission::firstOrCreate(
                [
                    'homework_id' => $homework->id,
                    'student_id' => $student->id,
                ],
                [
                    'status' => SubmissionStatus::PENDING,
                ]
            );

            if ($student->user) {
                $student->user->notify(new \App\Domains\Academic\Homework\Notifications\HomeworkPublishedNotification($homework));
            }
        }

        Log::info("Distributed Homework {$homework->id} to " . $students->count() . " students.");
    }
}

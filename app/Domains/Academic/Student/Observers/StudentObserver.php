<?php

namespace App\Domains\Academic\Student\Observers;

use App\Domains\Academic\Student\Models\Student;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StudentObserver
{
    /**
     * Handle the Student "updated" event.
     */
    public function updated(Student $student): void
    {
        $this->clearStudentCache($student->id);

        Log::info('Student cache cleared after update', [
            'student_id' => $student->id
        ]);
    }

    /**
     * Handle the Student "deleted" event.
     */
    public function deleted(Student $student): void
    {
        $this->clearStudentCache($student->id);

        Log::info('Student cache cleared after deletion', [
            'student_id' => $student->id
        ]);
    }

    /**
     * مسح الـ Cache الخاص بالطالب
     */
    protected function clearStudentCache(int $studentId): void
    {
        $cacheKeys = [
            "student.{$studentId}.performance.summary",
            "student.{$studentId}.performance.courses",
            "student.{$studentId}.performance.recommendations",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}

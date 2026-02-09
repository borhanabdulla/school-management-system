<?php

namespace App\Domains\Academic\Student\Actions;

use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Data\StudentUpdateData;
use App\Domains\Academic\Student\Services\StudentLookupService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class UpdateStudentAction
{
    public function execute(Student $student, StudentUpdateData $data): Student
    {
        $updatedStudent = DB::transaction(function () use ($student, $data) {
            $student->update($data->toArray());

            return $student->fresh();
        });

        $this->clearStudentCache($updatedStudent->id);
        StudentLookupService::clearCache(school()->activeYear()?->id);
        StudentLookupService::clearCache(null);

        return $updatedStudent;
    }

    /**
     * مسح جميع الـ Cache المتعلق بالطالب
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

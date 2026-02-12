<?php

namespace App\Domains\Academic\Student\Actions;

use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Services\StudentLookupService;
use App\Domains\Academic\Student\Exceptions\StudentDeleteBlockedException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteStudentAction
{
    public function execute(Student $student): void
    {
        // 1. Check for blockers
        $this->validateDeletion($student);

        // 2. Capture year ID for cache invalidation
        $yearId = $student->currentClassSection?->academic_year_id;

        DB::transaction(function () use ($student) {
            // 3. Delete Profile Photo
            if ($student->profile_photo_path) {
                Storage::disk('public')->delete($student->profile_photo_path);
            }

            // 4. Delete User Account (if exists)
            if ($student->user) {
                $student->user->delete();
            }

            // 5. Delete Student Record
            $student->delete();
        });

        // 6. Invalidate Cache
        StudentLookupService::clearCache($yearId);
        StudentLookupService::clearCache(null);
    }

    public function checkBlockers(Student $student): array
    {
        $blockers = [];

        // Rule 1: Financial Records (Invoices)
        if ($student->invoices()->exists()) {
            $blockers[] = 'لديه سجلات مالية (فواتير)';
        }

        // Rule 2: Attendance Records
        if ($student->attendances()->exists()) {
            $blockers[] = 'لديه سجلات حضور وغياب';
        }

        // Rule 3: Academic Results
        if ($student->annualResults()->exists()) {
            $blockers[] = 'لديه نتائج سنوية';
        }

        // Rule 4: Enrollments (Active or History)
        if ($student->enrollments()->count() > 0) {
            $blockers[] = 'لديه سجلات قيد دراسي (' . $student->enrollments()->count() . ' سنوات)';
        }

        if ($student->seatings()->exists()) {
            $blockers[] = 'لديه مقاعد اختبارات';
        }

        if ($student->promotions()->exists()) {
            $blockers[] = 'لديه قرارات ترحيل';
        }

        if ($student->healthConditions()->exists()) {
            $blockers[] = 'لديه حالات صحية مسجلة';
        }

        if ($student->previousHistories()->exists()) {
            $blockers[] = 'لديه سجلات دراسية سابقة';
        }

        $studentMarksCount = DB::table('student_marks')
            ->where('student_id', $student->id)
            ->count();

        if ($studentMarksCount > 0) {
            $blockers[] = 'لديه درجات دراسية (' . $studentMarksCount . ')';
        }

        $homeworkSubmissionsCount = DB::table('homework_submissions')
            ->where('student_id', $student->id)
            ->count();

        if ($homeworkSubmissionsCount > 0) {
            $blockers[] = 'لديه تسليمات واجبات (' . $homeworkSubmissionsCount . ')';
        }

        $termResultsCount = DB::table('term_results')
            ->where('student_id', $student->id)
            ->count();

        if ($termResultsCount > 0) {
            $blockers[] = 'لديه نتائج فصلية (' . $termResultsCount . ')';
        }

        return $blockers;
    }

    private function validateDeletion(Student $student): void
    {
        $blockers = $this->checkBlockers($student);

        if (!empty($blockers)) {
            throw new StudentDeleteBlockedException($blockers);
        }
    }
}

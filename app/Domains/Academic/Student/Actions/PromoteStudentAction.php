<?php

declare(strict_types=1);

namespace App\Domains\Academic\Student\Actions;

use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Student\Models\StudentEnrollment;
use App\Domains\Academic\Student\Services\StudentLookupService;
use App\Domains\Academic\Student\Services\StudentPlacementSyncService;
use Illuminate\Support\Facades\DB;
use App\Infrastructure\Exceptions\BusinessRuleException;
use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * PromoteStudentAction - ترحيل طالب لسنة جديدة
 */
class PromoteStudentAction
{
    public function __construct(
        protected StudentPlacementSyncService $placementSyncService
    ) {
    }

    /**
     * تنفيذ الترحيل
     */
    public function execute(
        Student $student,
        AcademicYear $nextYear,
        Grade $nextGrade,
        ?ClassSection $nextSection = null
    ): StudentEnrollment {

        // 1. التحقق من أن الطالب غير مسجل بالفعل في السنة القادمة
        $existingEnrollment = StudentEnrollment::where('student_id', $student->id)
            ->where('academic_year_id', $nextYear->id)
            ->exists();

        if ($existingEnrollment) {
            throw InvalidOperationException::alreadyDone("ترحيل الطالب {$student->full_name_ar} للسنة {$nextYear->name}");
        }

        $enrollment = DB::transaction(function () use ($student, $nextYear, $nextGrade, $nextSection) {
            // 2. إنشاء سجل القيد الجديد
            $enrollment = StudentEnrollment::create([
                'student_id' => $student->id,
                'academic_year_id' => $nextYear->id,
                'grade_id' => $nextGrade->id,
                'class_section_id' => $nextSection?->id,
                'enrollment_type' => 'returning',
                'status' => \App\Domains\Academic\Student\Enums\EnrollmentStatus::Active,
                'enrollment_date' => now(),
            ]);

            // 3. تحديث بيانات الطالب الحالية (للسهولة في الاستعلام)
            // ملاحظة: قد نفضل عدم تحديث هذا إلا عند بدء السنة فعلياً
            // لكن للتبسيط سنحدثه الآن أو نتركه حسب استراتيجية النظام
            // هنا سنحدثه ليعكس الوضع المستقبلي أو نتركه كما هو
            // في هذا التصميم، سنتركه كما هو، ونعتمد على Enrollment لمعرفة الصف في سنة معينة
            // ولكن Student model لديه current_grade_id، هل نحدثه؟
            // إذا كانت السنة القادمة هي "Active" (تم تفعيلها)، نحدثه.
            $this->placementSyncService->sync($enrollment);

            return $enrollment;
        });

        StudentLookupService::clearCache($nextYear->id);
        StudentLookupService::clearCache(null);

        return $enrollment;
    }
}

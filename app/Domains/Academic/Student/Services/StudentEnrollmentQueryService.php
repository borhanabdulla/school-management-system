<?php

namespace App\Domains\Academic\Student\Services;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\StudentEnrollment;
use App\Domains\Academic\Student\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Builder;

/**
 * StudentEnrollmentQueryService - مصدر الحقيقة للاستعلام عن enrollments
 * 
 * يعتمد على StudentEnrollment وليس على current_class_section_id
 * هذا يضمن ثبات معيار الإغلاق حتى أثناء الترحيل
 */
class StudentEnrollmentQueryService
{
    /**
     * الحالات التي تُعتبر "مؤهلة للإغلاق"
     * يمكن توسيعها لاحقاً بسهولة
     */
    protected array $eligibleStatuses = [
        EnrollmentStatus::Active,
    ];

    /**
     * عدد الطلاب المؤهلين للإغلاق في سنة معينة
     * يستخدم enrollment وليس current_section
     */
    public function eligibleForClosureCount(AcademicYear $year): int
    {
        return StudentEnrollment::where('academic_year_id', $year->id)
            ->whereIn('status', $this->getEligibleStatusValues())
            ->count();
    }

    /**
     * Subquery للـ student_ids المؤهلين - للاستخدام مع whereIn
     * لا يُحمّل البيانات في الذاكرة (بدون pluck)
     */
    public function eligibleStudentIdsSubquery(AcademicYear $year): Builder
    {
        return StudentEnrollment::where('academic_year_id', $year->id)
            ->whereIn('status', $this->getEligibleStatusValues())
            ->select('student_id');
    }

    /**
     * جلب الطلاب مع enrollment.grade_id للسنة المحددة
     * يستخدم JOIN بدلاً من whereHas لنتمكن من select أعمدة enrollment
     */
    public function studentsWithEnrollmentGrade(AcademicYear $year): Builder
    {
        return Student::query()
            ->join('student_enrollments', function ($join) use ($year) {
                $join->on('students.id', '=', 'student_enrollments.student_id')
                    ->where('student_enrollments.academic_year_id', '=', $year->id)
                    ->whereIn('student_enrollments.status', $this->getEligibleStatusValues());
            })
            ->select([
                'students.id',
                'students.full_name_ar',
                'students.status',
                'student_enrollments.grade_id as enrollment_grade_id',
                'student_enrollments.class_section_id as enrollment_section_id',
            ]);
    }

    /**
     * تحويل الـ enums إلى قيم string
     */
    protected function getEligibleStatusValues(): array
    {
        return array_map(fn($status) => $status->value, $this->eligibleStatuses);
    }

    /**
     * إضافة حالة للقائمة المؤهلة (للتوسع المستقبلي)
     */
    public function addEligibleStatus(EnrollmentStatus $status): self
    {
        if (!in_array($status, $this->eligibleStatuses)) {
            $this->eligibleStatuses[] = $status;
        }
        return $this;
    }
}

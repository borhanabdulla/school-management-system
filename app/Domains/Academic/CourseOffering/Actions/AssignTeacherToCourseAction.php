<?php

declare(strict_types=1);

namespace App\Domains\Academic\CourseOffering\Actions;

use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\CourseOffering\Exceptions\BusinessException;
use Illuminate\Support\Facades\DB;

/**
 * AssignTeacherToCourseAction - تعيين معلم لمقرر
 * 
 * تقوم بربط معلم بمادة في شعبة معينة (Course Offering).
 * 
 * @author School Dashboard Team
 * @version 2.0
 */
class AssignTeacherToCourseAction
{
    /**
     * تنفيذ التعيين
     * 
     * @param ClassSection $section الشعبة
     * @param Subject $subject المادة
     * @param Teacher $teacher المعلم
     * @return CourseOffering
     */
    public function execute(ClassSection $section, Subject $subject, Teacher $teacher): CourseOffering
    {
        return DB::transaction(function () use ($section, $subject, $teacher) {
            // 1. التحقق من أن المادة تدرس لهذا الصف
            // يمكن إضافة هذا التحقق لاحقاً إذا كانت العلاقة موجودة

            // 2. التحقق من نصاب المعلم (اختياري - يمكن إضافته هنا)
            // if ($teacher->hasReachedMaxLoad()) { ... }

            $activeYear = AcademicYear::query()
                ->where('status', AcademicYearStatus::Active)
                ->lockForUpdate()
                ->first();

            if (!$activeYear) {
                throw new BusinessException('لا توجد سنة أكاديمية نشطة.');
            }

            $activeTerm = Term::query()
                ->where('academic_year_id', $activeYear->id)
                ->where('status', TermStatus::Active)
                ->lockForUpdate()
                ->first();

            // 3. إنشاء أو تحديث المقرر
            $offering = CourseOffering::updateOrCreate(
                [
                    'class_section_id' => $section->id,
                    'subject_id' => $subject->id,
                    'academic_year_id' => $activeYear->id,
                ],
                [
                    'teacher_id' => $teacher->id,
                    'term_id' => $activeTerm?->id,
                ]
            );

            return $offering;
        });
    }
}

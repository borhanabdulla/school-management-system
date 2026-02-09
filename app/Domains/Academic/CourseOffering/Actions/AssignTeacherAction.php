<?php

namespace App\Domains\Academic\CourseOffering\Actions;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Subject\Models\GradeSubject;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\HR\Teacher\Models\Teacher;
use Illuminate\Validation\ValidationException;

class AssignTeacherAction
{
    // ✅ تم التعديل: تفعيل تمرير الترم ليتم استخدامه في القيد الفريد
    public function execute(int $classSectionId, int $subjectId, int $teacherId, int $academicYearId, ?int $termId = null): void
    {
        $section = ClassSection::findOrFail($classSectionId);

        // 1. التحقق من أن المادة موجودة في منهج الصف
        $gradeSubject = GradeSubject::where('grade_id', $section->grade_id)
            ->where('subject_id', $subjectId)
            ->where('is_active', true)
            ->first();

        if (!$gradeSubject) {
            throw ValidationException::withMessages([
                'subject_id' => 'هذه المادة غير موجودة في منهج هذا الصف.'
            ]);
        }

        // ✅ 2. Defense-in-depth: التحقق من وجود المعلم ونشاطه
        $teacher = Teacher::with('staff')->find($teacherId);

        if (!$teacher) {
            throw ValidationException::withMessages([
                'teacher_id' => 'المعلم المحدد غير موجود.'
            ]);
        }

        if ($teacher->staff?->status !== 'active') {
            throw ValidationException::withMessages([
                'teacher_id' => 'المعلم المحدد غير نشط حالياً.'
            ]);
        }

        // 3. إنشاء أو تحديث التعيين
        // ✅ تم التعديل: إضافة term_id لمصفوفة البحث ليتوافق مع الفهرس الفريد الجديد
        CourseOffering::updateOrCreate(
            [
                'academic_year_id' => $academicYearId,
                'class_section_id' => $classSectionId,
                'subject_id' => $subjectId,
                'term_id' => $termId, // ✅ مفتاح البحث الجديد
            ],
            [
                'teacher_id' => $teacherId,
            ]
        );
    }
}
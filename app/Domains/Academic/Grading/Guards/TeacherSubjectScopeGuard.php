<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Guards;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Exceptions\GradingException;
use App\Domains\Shared\Models\User;

/**
 * TeacherSubjectScopeGuard - التحقق من صلاحية المعلم للوصول لمادة معينة
 * 
 * يتحقق من أن المعلم يدرّس المادة المحددة في الترم والصف المطلوبين
 * بناءً على CourseOffering الموجود في الـDB
 * 
 * @responsibility Scope enforcement للمعلمين
 */
class TeacherSubjectScopeGuard
{
    /**
     * التحقق من أن المعلم يدرّس هذه المادة في هذا الترم والصف
     * 
     * @param User $user المستخدم (يجب أن يكون معلماً)
     * @param int $subjectId ID المادة
     * @param int $gradeId ID الصف
     * @param int $termId ID الترم
     * @return bool
     * @throws GradingException إذا لم يكن المستخدم معلماً أو لا يدرس المادة
     */
    public function authorize(
        User $user,
        int $subjectId,
        int $gradeId,
        int $termId
    ): void {
        // 1. التحقق من أن المستخدم معلم
        $teacher = $user->teacher;  // Uses actual relationship from User model

        if (!$teacher) {
            throw new GradingException('المستخدم ليس معلماً.');
        }

        // 2. البحث عن CourseOffering للمعلم في هذه المادة/الترم/الصف
        // نستخدم classSection->grade_id بدلاً من grade_id مباشرة
        $offering = CourseOffering::query()
            ->where('teacher_id', $teacher->id)
            ->where('term_id', $termId)
            ->where('subject_id', $subjectId)
            ->whereHas('classSection', function ($query) use ($gradeId) {
                $query->where('grade_id', $gradeId);
            })
            ->first();

        if (!$offering) {
            throw new GradingException(
                'ليس لديك صلاحية الوصول لهذه المادة. يجب أن تكون معلماً مسجلاً لهذه المادة في هذا الترم والصف.'
            );
        }
    }

    /**
     * التحقق السريع - يرجع true/false بدون exception
     * 
     * @param User $user
     * @param int $subjectId
     * @param int $gradeId  
     * @param int $termId
     * @return bool
     */
    public function check(
        User $user,
        int $subjectId,
        int $gradeId,
        int $termId
    ): bool {
        try {
            $this->authorize($user, $subjectId, $gradeId, $termId);
            return true;
        } catch (GradingException $e) {
            return false;
        }
    }

    /**
     * الحصول على جميع المواد التي يدرسها المعلم في ترم معين
     * 
     * @param User $user
     * @param int $termId
     * @return \Illuminate\Support\Collection [subject_id => CourseOffering]
     */
    public function getTeacherSubjectsInTerm(User $user, int $termId)
    {
        $teacher = $user->teacher;

        if (!$teacher) {
            return collect();
        }

        return CourseOffering::query()
            ->where('teacher_id', $teacher->id)
            ->where('term_id', $termId)
            ->with(['subject', 'classSection.grade'])
            ->get()
            ->keyBy('subject_id');
    }
}

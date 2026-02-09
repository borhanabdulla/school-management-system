<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Actions;

use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Timetable\Models\TimeSlot;
use App\Domains\Academic\Timetable\Exceptions\CannotDeleteTimetableWithAttendanceException;
use App\Domains\Academic\Services\AcademicWriteGuard;
use App\Infrastructure\Exceptions\InvalidOperationException;
use Illuminate\Support\Facades\DB;
use App\Domains\Academic\Timetable\Data\SessionData;

/**
 * AssignSessionAction - تسجيل حصة في الجدول
 * 
 * @security-critical ⚠️  Phase 1: Added guard to prevent changing course_offering with attendance
 * 
 * يقوم هذا الإجراء بـ:
 * 1. إنشاء أو تحديث CourseOffering (ارتباط المادة بالمعلم والشعبة)
 * 2. إنشاء أو تحديث Timetable (ارتباط الحصة بالمادة والشعبة)
 * 
 * Guard Added:
 * - Checks if timetable exists and has attendance before allowing course_offering change
 * - Throws exception if trying to change course_offering_id for sessions with attendance
 */
class AssignSessionAction
{
    /**
     * تسجيل حصة في الجدول
     *
     * @throws InvalidOperationException If course_offering change is attempted on session with attendance
     */
    public function execute(
        int $yearId,
        int $termId,
        int $sectionId,
        int $subjectId,
        int $teacherId,
        int $slotId
    ): Timetable {
        // 🛡️ PR-1: Guard against closed year and term
        app(AcademicWriteGuard::class)->assertWritable($yearId, $termId);

        return DB::transaction(function () use ($yearId, $termId, $sectionId, $subjectId, $teacherId, $slotId) {
            // التحقق من وجود الحصة والشعبة
            $section = ClassSection::findOrFail($sectionId);
            $slot = TimeSlot::with('template')->findOrFail($slotId);

            // التحقق من أن الحصة مرتبطة بالقالب الصحيح للشعبة
            if ($slot->template) {
                $isValid = DB::table('grade_timetable_template')
                    ->where('grade_id', $section->grade_id)
                    ->where('academic_year_id', $yearId)
                    ->where('template_id', $slot->template->id)
                    ->exists();

                if (!$isValid) {
                    throw InvalidOperationException::make('الحصة المحددة غير مرتبطة بالقالب الصحيح لهذه الشعبة.');
                }
            }

            // 1. تحديث أو إنشاء CourseOffering
            // ✅ تم التعديل: البحث يشمل term_id لضمان استقلالية كل ترم
            $courseOffering = CourseOffering::updateOrCreate(
                [
                    'academic_year_id' => $yearId,
                    'subject_id' => $subjectId,
                    'class_section_id' => $sectionId,
                    'term_id' => $termId, // ✅ مضاف لمسار البحث لجعله فريداً للترم
                ],
                [
                    'teacher_id' => $teacherId,
                ]
            );

            // 3. ربط الحصة بالمادة
            return Timetable::updateOrCreate(
                [
                    'class_section_id' => $sectionId,
                    'time_slot_id' => $slotId,
                    'term_id' => $termId, // ✅ PR0: تمييز الجدول بالترم
                ],
                [
                    'course_offering_id' => $courseOffering->id
                ]
            );
        });
    }
}

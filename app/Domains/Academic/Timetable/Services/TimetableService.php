<?php

namespace App\Domains\Academic\Timetable\Services;

use App\Domains\Academic\Timetable\Models\Timetable;
use Illuminate\Support\Collection;

class TimetableService
{
    /**
     * التحقق من تعارض المعلم في حصة معينة
     * ✅ PR-3: تم إضافة فلتر السنة الدراسية لمنع التعارض مع الجداول القديمة
     */
    public function checkTeacherConflict(int $teacherId, int $slotId, int $academicYearId, int $termId, ?int $excludeSectionId = null): ?array
    {
        $conflict = Timetable::with([
            'classSection:id,name,grade_id',
            'classSection.grade:id,name',
            'courseOffering:id,subject_id',
            'courseOffering.subject:id,name'
        ])
            ->where('time_slot_id', $slotId)
            ->where('term_id', $termId) // ✅ PR0: Check conflict in SAME TERM
            ->when($excludeSectionId, fn($q) => $q->where('class_section_id', '!=', $excludeSectionId))
            // ✅ الفلترة حسب السنة الدراسية عبر المقرر (still useful context)
            ->whereHas('courseOffering', fn($q) => $q->where('teacher_id', $teacherId)->where('academic_year_id', $academicYearId))
            ->first();

        if ($conflict) {
            $sectionName = $conflict->classSection->grade->name . ' - ' . $conflict->classSection->name;
            $subjectName = $conflict->courseOffering->subject->name ?? 'مادة';

            return [
                'has_conflict' => true,
                'message' => "⚠️ هذا المعلم لديه حصة في نفس الوقت",
                'details' => "الشعبة: {$sectionName} - المادة: {$subjectName}",
            ];
        }

        return null;
    }

    /**
     * التحقق من تكرار المادة في نفس اليوم لنفس الشعبة
     */
    public function checkSubjectDailyRepetition(int $subjectId, int $sectionId, int $dayOfWeek, int $academicYearId, int $termId, ?int $excludeSlotId = null): ?array
    {
        $exists = Timetable::where('class_section_id', $sectionId)
            ->where('term_id', $termId) // ✅ PR0: Check repetition in SAME TERM
            // ✅ الفلترة حسب السنة الدراسية
            ->whereHas('courseOffering', fn($q) => $q->where('subject_id', $subjectId)->where('academic_year_id', $academicYearId))
            ->whereHas('timeSlot', fn($q) => $q->where('day_of_week', $dayOfWeek))
            ->when($excludeSlotId, fn($q) => $q->where('time_slot_id', '!=', $excludeSlotId))
            ->exists();

        if ($exists) {
            return [
                'has_conflict' => true,
                'message' => "⚠️ تنبيه: هذه المادة مضافة بالفعل في هذا اليوم",
                'details' => "يفضل توزيع المواد على أيام مختلفة",
            ];
        }

        return null;
    }
}

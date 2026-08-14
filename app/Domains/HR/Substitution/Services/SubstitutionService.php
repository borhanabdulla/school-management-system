<?php

namespace App\Domains\HR\Substitution\Services;

use App\Domains\HR\Substitution\Models\Substitution;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Timetable\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SubstitutionService
{
    /**
     * الحصول على البدلاء المقترحين لحصة معينة
     * 
     * @param int $timetableId الحصة المتأثرة
     * @param Carbon $date تاريخ البحث
     * @return Collection قائمة مرتبة من المعلمين المقترحين
     */
    /**
     * البحث عن بدلاء مع تطبيق فلاتر
     * 
     * @param int $timetableId الحصة المتأثرة
     * @param Carbon $date تاريخ البحث
     * @param array $filters الفلاتر (تخصص، توفر، حمل)
     * @return Collection قائمة المعلمين
     * 
     */
    public function getSuggestedSubstitutes(int $timetableId, Carbon $date, array $filters = []): Collection
    {
        $timetable = Timetable::with(['courseOffering.subject', 'timeSlot'])->find($timetableId);

        if (!$timetable) {
            return collect();
        }

        $targetSubject = $timetable->courseOffering?->subject; // جلب المادة المطلوبة   
        $timeSlot = $timetable->timeSlot;// جلب الجدول الدراسي
        $dayOfWeek = $timeSlot->day_of_week;// جلب اليوم

        // دمج الفلاتر الافتراضية
        $filters = array_merge([
            'same_specialization' => true, // الافتراضي: نفس التخصص فقط
            'ignore_busy' => false,        // الافتراضي: استبعاد المشغولين
            'max_daily_load' => 6,         // الافتراضي: الحد الأقصى للحمل
        ], $filters);

        // جلب جميع المعلمين النشطين
        // ✅ يستخدم status بدلاً من termination_date (غير موجود في DB)
        $query = Teacher::with(['staff', 'courseOfferings.subject'])
            ->active();

        // 1. فلتر التخصص (على مستوى الاستعلام لتقليل البيانات)
        if ($filters['same_specialization'] && $targetSubject) { // هنا اذا الفلتر نفس الماده ونفس التخصص 
            $query->whereHas('courseOfferings', function ($q) use ($targetSubject) {
                $q->where('subject_id', $targetSubject->id);
            });
        }

        $candidates = $query->get();
        $suggestions = collect();

        foreach ($candidates as $teacher) {
            // تخطي المعلم الأصلي الغائب 
            if ($teacher->id === $timetable->courseOffering?->teacher_id) {
                continue;
            }

            // التحقق من التوفر
            // ✅ PR-3: تمرير السنة الدراسية للتحقق من التعارض في السنة الصحيحة
            $academicYearId = $timetable->courseOffering->academic_year_id;
            $isBusy = $this->hasTimeConflict($teacher->id, $timeSlot->id, $dayOfWeek, $academicYearId);
            if ($filters['ignore_busy'] === false && $isBusy) {
                continue;
            }

            // التحقق من الحمل اليومي
            $dailyLoad = $this->getTeacherDailyLoad($teacher->id, $date);
            if ($dailyLoad >= $filters['max_daily_load']) {
                continue;
            }

            // حساب بيانات العرض
            $specialization = $this->getTeacherSpecialization($teacher);
            $isSameSpec = $this->isSameSpecialization($teacher, $targetSubject);

            $suggestions->push([
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->full_name,
                'specialization' => $specialization,
                'daily_load' => $dailyLoad,
                'is_same_specialization' => $isSameSpec,
                'is_busy' => $isBusy,
                'match_reason' => $isSameSpec ? 'نفس التخصص' : 'تخصص مختلف',
            ]);
        }

        // ترتيب النتائج: نفس التخصص أولاً -> الأقل حملاً
        return $suggestions->sortByDesc(function ($item) {
            return [
                $item['is_same_specialization'] ? 1 : 0,
                -$item['daily_load'],
            ];
        })->values();
    }



    //     public function getSuggestedSubstitutes(int $timetableId, Carbon $date, array $filters = []): Collection
// {
//     $timetable = Timetable::with('timeSlot')->findOrFail($timetableId);
//     $targetSubjectId = $timetable->courseOffering?->subject_id;
//     $timeSlotId = $timetable->timeSlot->id;

    //     // دمج كل شيء في سطر واحد احترافي
//     return Teacher::query()
//         ->active()                                   // (Scope 1)
//         ->when($filters['same_specialization'], function($q) use ($targetSubjectId) {
//             return $q->inSubject($targetSubjectId);  // (Scope 2)
//         })
//         ->availableAt($date, $timeSlotId)            // (Scope 3 - الـ SQL المعقد مخفي هنا)
//         ->with(['staff'])                            // جلب بيانات الاسم للـ UI
//         ->get();
// }

    /**
     * هل المعلم يدرس نفس المادة؟
     */
    protected function isSameSpecialization(Teacher $teacher, $targetSubject): bool
    {
        if (!$targetSubject)
            return false;
        return $teacher->courseOfferings->contains('subject_id', $targetSubject->id);
    }

    /**
     * الحصول على تخصص المعلم الرئيسي
     */
    protected function getTeacherSpecialization(Teacher $teacher): string
    {
        $subjects = $teacher->courseOfferings->pluck('subject.name')->filter()->unique();
        return $subjects->first() ?? 'غير محدد';
    }

    /**
     * تعيين بديل
     */
    public function assignSubstitute(
        int $timetableId,
        int $substituteTeacherId,
        int $originalTeacherId,
        Carbon $date,
        ?int $leaveRequestId = null,
        bool $isPaid = false,
        ?int $createdBy = null
    ): Substitution {
        if ($createdBy === null) {
            throw new \InvalidArgumentException('created_by is required for substitution assignment.');
        }

        return DB::transaction(function () use ($timetableId, $substituteTeacherId, $originalTeacherId, $date, $leaveRequestId, $isPaid, $createdBy) {
            $alreadyAssigned = Substitution::where('timetable_id', $timetableId)
                ->whereDate('date', $date)
                ->whereIn('status', ['pending', 'confirmed'])
                ->exists();

            if ($alreadyAssigned) {
                throw new \DomainException('تم تعيين بديل لهذه الحصة في نفس التاريخ بالفعل.');
            }

            return Substitution::create([
                'date' => $date,
                'timetable_id' => $timetableId,
                'original_teacher_id' => $originalTeacherId,
                'substitute_teacher_id' => $substituteTeacherId,
                'leave_request_id' => $leaveRequestId,
                'status' => 'pending',
                'is_paid' => $isPaid,
                'created_by' => $createdBy,
            ]);
        });
    }

    /**
     * الحصول على البدائل المعينة لتاريخ معين
     */
    public function getSubstitutionsForDate(Carbon $date): Collection
    {
        return Substitution::with([
            'timetable.courseOffering.subject',
            'timetable.courseOffering.classSection',
            'timetable.timeSlot',
            'originalTeacher.staff',
            'substituteTeacher.staff',
        ])
            ->forDate($date)
            ->get();
    }

    /**
     * التحقق من وجود بديل معين لحصة
     */
    public function hasSubstitute(int $timetableId, Carbon $date): bool
    {
        return Substitution::where('timetable_id', $timetableId)
            ->whereDate('date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();
    }

    /**
     * الحصول على البديل المعين لحصة
     */
    public function getSubstitute(int $timetableId, Carbon $date): ?Substitution
    {
        return Substitution::with(['substituteTeacher.staff'])
            ->where('timetable_id', $timetableId)
            ->whereDate('date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();
    }

    /**
     * الحصول على البدائل لمجموعة من الحصص دفعة واحدة
     * 
     * @param array $timetableIds
     * @param Carbon $date
     * @return Collection
     */
    public function getSubstitutionsForTimetables(array $timetableIds, Carbon $date): Collection
    {
        return Substitution::with(['substituteTeacher.staff'])
            ->whereIn('timetable_id', $timetableIds)
            ->whereDate('date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->keyBy('timetable_id');
    }
    /**
     * الحصول على البدائل المستحقة للدفع لموظف في فترة معينة
     */
    public function getPayableSubstitutions(int $staffId, Carbon $start, Carbon $end): Collection
    {
        // نحتاج أولاً لمعرفة Teacher ID المرتبط بهذا Staff ID
        $teacher = Teacher::where('staff_id', $staffId)->first();

        if (!$teacher) {
            return collect();
        }

        return Substitution::query()
            ->where('substitute_teacher_id', $teacher->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->where('status', 'confirmed') // فقط المؤكدة
            ->where('is_paid', true)       // فقط المدفوعة
            ->with(['timetable.courseOffering.subject'])
            ->get();
    }

    /**
     * التحقق من وجود تعارض في الوقت للمعلم
     * ✅ PR-3: فلترة بالسنة الدراسية
     */
    protected function hasTimeConflict(int $teacherId, int $timeSlotId, string $dayOfWeek, int $academicYearId): bool
    {
        // 1. التحقق من الجدول الدراسي العادي
        $hasLesson = Timetable::whereHas(
            'courseOffering',
            fn($q) =>
            $q->where('teacher_id', $teacherId)->where('academic_year_id', $academicYearId)
        )
            ->where('time_slot_id', $timeSlotId)
            ->exists();

        if ($hasLesson)
            return true;

        // 2. التحقق من وجود بديل معتمد في نفس الوقت
        // ملاحظة: البدائل مرتبطة بحصص، والحصص مرتبطة بمقررات، والمقررات بسنوات
        return Substitution::where('substitute_teacher_id', $teacherId)
            ->where('status', 'confirmed')
            ->whereHas('timetable.courseOffering', fn($q) => $q->where('academic_year_id', $academicYearId))
            ->whereHas('timetable', fn($q) => $q->where('time_slot_id', $timeSlotId))
            ->exists();
    }

    /**
     * الحصول على الحمل اليومي للمعلم (عدد الحصص)
     */
    protected function getTeacherDailyLoad(int $teacherId, Carbon $date): int
    {
        $dayOfWeek = strtolower($date->format('l'));
        $shortDay = substr($dayOfWeek, 0, 3);

        // 1. الحصص العادية في هذا اليوم
        $regularLessons = Timetable::whereHas('courseOffering', fn($q) => $q->where('teacher_id', $teacherId))
            ->whereHas('timeSlot', fn($q) => $q->where('day_of_week', $shortDay))
            ->count();

        // 2. البدائل المعتمدة في هذا اليوم
        $substitutions = Substitution::where('substitute_teacher_id', $teacherId)
            ->whereDate('date', $date)
            ->where('status', 'confirmed')
            ->count();

        return $regularLessons + $substitutions;
    }

    public function teacherHasClassesOnDate(int $teacherId, Carbon $date): bool
    {
        $dayOfWeek = strtolower($date->format('l'));
        $shortDay = substr($dayOfWeek, 0, 3);

        return Timetable::whereHas('courseOffering', fn($q) => $q->where('teacher_id', $teacherId))
            ->whereHas('timeSlot', fn($q) => $q->where('day_of_week', $shortDay))
            ->exists();
    }
}

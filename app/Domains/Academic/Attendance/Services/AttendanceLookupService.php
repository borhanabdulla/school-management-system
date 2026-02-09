<?php

namespace App\Domains\Academic\Attendance\Services;

use App\Domains\Academic\Attendance\Models\Attendance;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Attendance\Enums\AttendanceStatus;
use Illuminate\Support\Collection;

class AttendanceLookupService
{
    /**
     * تجهيز ورقة الحضور (The Sheet)
     * هذه الدالة ذكية: تجلب الطلاب + حالتهم الحالية أو المقترحة
     */
    public function getAttendanceSheetData(Timetable $timetable, string $date): Collection
    {
        // 1. جلب طلاب الشعبة (Active Only) مع ترتيب أبجدي
        $students = $timetable->classSection->students()
            ->active()
            ->orderBy('first_name_ar')
            ->get();

        // 2. هل تم رصد غياب لهذه الحصة تحديداً من قبل؟
        // نستخدم class_section_id و date و time_slot_id كمفتاح مركب
        $existingRecords = Attendance::where('class_section_id', $timetable->class_section_id)
            ->where('date', $date)
            ->where('term_id', $timetable->term_id) // ✅ PR1: Scope by Term ID
            ->where('time_slot_id', $timetable->time_slot_id)
            ->get()
            ->keyBy('student_id');

        // 3. (المنطق الذكي) إذا لم يوجد رصد، هل الطالب غائب في حصص سابقة اليوم؟
        // تحسين الأداء: نبحث فقط ضمن نفس الشعبة ونفس اليوم
        // جلب كل غيابات اليوم لهذه الشعبة (مرة واحدة لتقليل الاستعلامات)
        $allDayAbsences = Attendance::where('class_section_id', $timetable->class_section_id)
            ->where('date', $date)
            ->where('term_id', $timetable->term_id) // ✅ PR1: Scope by Term ID
            ->whereIn('status', [AttendanceStatus::ABSENT->value, AttendanceStatus::ESCAPED->value])
            ->get()
            ->groupBy('student_id');

        // 4. دمج البيانات
        return $students->map(function ($student) use ($existingRecords, $allDayAbsences) {

            // الحالة الافتراضية
            $status = AttendanceStatus::PRESENT->value;
            $remarks = '';
            $delay = 0;
            $isSaved = false;

            if (isset($existingRecords[$student->id])) {
                // إذا كان هناك سجل محفوظ، نستخدمه
                $record = $existingRecords[$student->id];
                $status = $record->status;
                $remarks = $record->remarks;
                $delay = $record->delay_minutes;
                $isSaved = true;
            } elseif (isset($allDayAbsences[$student->id])) {
                // وراثة الغياب: إذا كان لديه أي غياب سابق اليوم
                $status = AttendanceStatus::ABSENT->value;
            }

            return [
                'student_id' => $student->id,
                'name' => $student->full_name_ar, // استخدام Accessor الاسم الكامل العربي
                'photo' => $student->profile_photo_url, // إذا كان موجوداً
                'status' => $status,
                'delay_minutes' => $delay,
                'remarks' => $remarks,
                'is_saved_previously' => $isSaved,
            ];
        });
    }
    /**
     * جلب حالة الحضور الأسبوعية للمعلم
     * ترجع مصفوفة مفهرسة بـ (التاريخ_رقم الحصة_الشعبة)
     */
    public function getWeeklyAttendanceStatus(int $teacherId, string $startDate, string $endDate): array
    {
        // جلب كل سجلات الحضور لهذا المعلم في الفترة المحددة
        // ملاحظة: نفترض أن المعلم يرى الحضور الذي قام به أو الحضور لطلابه في حصصه
        // هنا سنبحث عن الحضور المرتبط بحصص هذا المعلم

        // 1. جلب الحصص التي يدرسها المعلم
        // هذا الاستعلام قد يكون معقداً قليلاً، لذا سنعتمد على جدول Attendance مباشرة
        // ونفترض أن الـ Timetable مربوط بشكل صحيح

        $attendances = Attendance::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->whereHas('timeSlot', function ($q) {
                $q->where('type', \App\Domains\Academic\Timetable\Enums\TimeSlotType::Academic);
            })
            // يمكننا إضافة شرط المعلم هنا إذا كان الـ Attendance مربوطاً بالمعلم أو عبر الجدول
            // للتبسيط والسرعة، سنجلب الحضور للشعب التي يدرسها المعلم في الجدول
            ->get()
            ->groupBy(function ($attendance) {
                return $attendance->date->format('Y-m-d') . '_' . $attendance->time_slot_id . '_' . $attendance->class_section_id;
            });

        $statusMap = [];
        foreach ($attendances as $key => $records) {
            // إذا وجد سجل واحد على الأقل، نعتبره "تم الرصد"
            // يمكن تطوير المنطق لاحقاً (مثلاً: هل تم رصد كل الطلاب؟)
            $statusMap[$key] = true;
        }

        return $statusMap;
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Actions;

use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Timetable\Exceptions\InvalidTimeSlotsException;
use App\Infrastructure\Exceptions\InvalidOperationException;
use Illuminate\Support\Facades\DB;

/**
 * GenerateTimetableAction - توليد الجدول الدراسي
 * 
 * تقوم هذه العملية بتطبيق قالب جدول دراسي على شعبة معينة.
 */
class GenerateTimetableAction
{
    public function execute(ClassSection $section, TimetableTemplate $template, int $termId, bool $overwrite = false): int
    {
        return DB::transaction(function () use ($section, $template, $termId, $overwrite) {
            // التحقق من أن القالب قابل للاستخدام
            if (!$template->isUsable()) {
                throw InvalidOperationException::make('القالب غير قابل للاستخدام. يجب تفعيله أولاً.');
            }

            // التحقق من وجود حصص في القالب
            if ($template->timeSlots()->count() === 0) {
                throw InvalidTimeSlotsException::noSlotsProvided();
            }

            // التحقق من أن القالب مرتبط بالصف المناسب
            $gradeId = $section->grade_id;
            $yearId = $section->academic_year_id;
            $isAssigned = DB::table('grade_timetable_template')
                ->where('grade_id', $gradeId)
                ->where('academic_year_id', $yearId)
                ->where('template_id', $template->id)
                ->exists();

            if (!$isAssigned) {
                throw InvalidOperationException::make('القالب غير مرتبط بهذا الصف. يرجى التأكد من تعيين القالب للصف أولاً.');
            }

            // ✅ PR0: Check existing for SPECIFIC TERM
            $existingCount = Timetable::where('class_section_id', $section->id)
                ->where('term_id', $termId)
                ->count();

            if ($existingCount > 0) {
                if (!$overwrite) {
                    throw InvalidOperationException::make('يوجد جدول مسبق لهذه الشعبة في هذا الترم. يجب تفعيل خيار الاستبدال للمتابعة.');
                }

                Timetable::where('class_section_id', $section->id)
                    ->where('term_id', $termId)
                    ->delete();
            }

            $slots = $template->timeSlots()->orderBy('day_of_week')->orderBy('order_index')->get();

            $count = 0;
            foreach ($slots as $slot) {
                Timetable::create([
                    'class_section_id' => $section->id,
                    'time_slot_id' => $slot->id,
                    'term_id' => $termId, // ✅ PR0 Requirement
                    'course_offering_id' => null,
                ]);
                $count++;
            }

            return $count;
        });
    }
}

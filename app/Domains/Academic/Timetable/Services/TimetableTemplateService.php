<?php

namespace App\Domains\Academic\Timetable\Services;

use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use App\Domains\Academic\Timetable\Models\TimeSlot;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Timetable\Data\TimetableTemplateData;
use App\Domains\Academic\Timetable\Data\SlotGeneratorConfig;
use App\Domains\Academic\Timetable\Data\TimeSlotData;
use App\Domains\Academic\Timetable\Exceptions\TemplateNotEditableException;
use App\Domains\Academic\Timetable\Exceptions\InvalidTimeSlotsException;
use App\Domains\Academic\Timetable\Exceptions\GradeAlreadyAssignedException;
use App\Domains\Academic\Timetable\Enums\TemplateStatus;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Timetable\Validators\TimetableSlotValidator;
use App\Domains\Shared\Enums\DayOfWeek;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class TimetableTemplateService
{
    /**
     * إنشاء قالب جديد
     */
    public function create(TimetableTemplateData $data): TimetableTemplate
    {
        return DB::transaction(function () use ($data) {
            // التحقق من عدم تداخل الصفوف
            $this->validateGradeAssignments($data->gradeIds, $data->academicYearId);

            // إنشاء القالب
            $template = TimetableTemplate::create($data->toModelArray());

            // حفظ الحصص
            $this->syncTimeSlots($template, $data->slots);

            // ربط الصفوف
            $this->syncGrades($template, $data->gradeIds, $data->academicYearId);

            Log::info('Timetable template created', ['id' => $template->id, 'name' => $template->name]);

            return $template->fresh(['timeSlots', 'grades']);
        });
    }

    /**
     * تحديث قالب موجود
     */
    public function update(TimetableTemplate $template, TimetableTemplateData $data): TimetableTemplate
    {
        // التحقق من قابلية التعديل
        if (!$template->isEditable()) {
            throw new TemplateNotEditableException($template->id, $template->status);
        }

        return DB::transaction(function () use ($template, $data) {
            // التحقق من عدم تداخل الصفوف (باستثناء القالب الحالي)
            $this->validateGradeAssignments($data->gradeIds, $data->academicYearId, $template->id);

            // تحديث القالب
            $template->update($data->toModelArray());

            // تحديث الحصص
            $this->syncTimeSlots($template, $data->slots);

            // تحديث الصفوف
            $this->syncGrades($template, $data->gradeIds, $data->academicYearId);

            Log::info('Timetable template updated', ['id' => $template->id]);

            return $template->fresh(['timeSlots', 'grades']);
        });
    }

    /**
     * تفعيل القالب
     */
    public function activate(TimetableTemplate $template): void
    {
        if ($template->status === TemplateStatus::Active) {
            return;
        }

        // التحقق من وجود حصص
        if ($template->timeSlots()->count() === 0) {
            throw InvalidTimeSlotsException::noSlotsProvided();
        }

        $template->update(['status' => TemplateStatus::Active]);

        Log::info('Timetable template activated', ['id' => $template->id]);
    }

    /**
     * أرشفة القالب
     */
    public function archive(TimetableTemplate $template): void
    {
        $template->update(['status' => TemplateStatus::Archived]);

        Log::info('Timetable template archived', ['id' => $template->id]);
    }

    /**
     * حذف القالب
     */
    public function delete(TimetableTemplate $template): void
    {
        if ($template->status === TemplateStatus::Active) {
            throw new TemplateNotEditableException(
                $template->id,
                $template->status,
                "لا يمكن حذف قالب نشط. قم بأرشفته أولاً."
            );
        }

        DB::transaction(function () use ($template) {
            // حذف الروابط والحصص (سيتم تلقائياً بسبب cascadeOnDelete)
            $template->delete();

            Log::info('Timetable template deleted', ['id' => $template->id]);
        });
    }

    /**
     * نسخ قالب موجود
     */
    public function duplicate(TimetableTemplate $template, string $newName): TimetableTemplate
    {
        return DB::transaction(function () use ($template, $newName) {
            // نسخ القالب
            $newTemplate = $template->replicate();
            $newTemplate->name = $newName;
            $newTemplate->status = TemplateStatus::Draft;
            $newTemplate->is_default = false;
            $newTemplate->save();

            // نسخ الحصص
            foreach ($template->timeSlots as $slot) {
                $newSlot = $slot->replicate();
                $newSlot->template_id = $newTemplate->id;
                $newSlot->save();
            }

            Log::info('Timetable template duplicated', [
                'original_id' => $template->id,
                'new_id' => $newTemplate->id
            ]);

            return $newTemplate->fresh(['timeSlots']);
        });
    }

    /**
     * توليد حصص باستخدام المولد الذكي
     */
    public function generateSlots(SlotGeneratorConfig $config, array $workingDays): array
    {
        return $config->generateSlotsForAllDays($workingDays);
    }

    /**
     * تطبيق حصص يوم على جميع الأيام
     */
    public function applyDayToAllDays(TimetableTemplate $template, int $sourceDay): void
    {
        if (!$template->isEditable()) {
            throw new TemplateNotEditableException($template->id, $template->status);
        }

        DB::transaction(function () use ($template, $sourceDay) {
            // جلب حصص اليوم المصدر
            $sourceSlots = $template->getSlotsForDay($sourceDay);

            if ($sourceSlots->isEmpty()) {
                throw InvalidTimeSlotsException::noSlotsProvided();
            }

            // حذف حصص باقي الأيام
            $template->timeSlots()
                ->where('day_of_week', '!=', $sourceDay)
                ->delete();

            // نسخ إلى باقي الأيام
            foreach ($template->working_days as $dayKey) {
                $day = is_string($dayKey) ? DayOfWeek::from($dayKey)->value : $dayKey;

                if ($day === $sourceDay)
                    continue;

                foreach ($sourceSlots as $slot) {
                    TimeSlot::create([
                        'template_id' => $template->id,
                        'day_of_week' => $day,
                        'label' => $slot->label,
                        'order_index' => $slot->order_index,
                        'start_time' => $slot->start_time,
                        'end_time' => $slot->end_time,
                        'type' => $slot->type,
                    ]);
                }
            }

            Log::info('Applied day slots to all days', [
                'template_id' => $template->id,
                'source_day' => $sourceDay
            ]);
        });
    }

    /**
     * التحقق من صحة توقيت الحصص
     */
    public function validateSlots(array $slots): array
    {
        $errors = [];
        $slotsByDay = collect($slots)->groupBy('day_of_week');

        foreach ($slotsByDay as $day => $daySlots) {
            $sorted = $daySlots->sortBy('order_index')->values();

            for ($i = 0; $i < count($sorted) - 1; $i++) {
                $current = $sorted[$i];
                $next = $sorted[$i + 1];

                $currentEnd = strtotime($current['end_time'] ?? $current->endTime ?? '');
                $nextStart = strtotime($next['start_time'] ?? $next->startTime ?? '');

                if ($currentEnd > $nextStart) {
                    $nextIndex = $i + 1;
                    $errors[] = "تداخل في اليوم {$day}: الحصة #{$i} تنتهي بعد بدء الحصة #{$nextIndex}";
                }
            }
        }

        return $errors;
    }

    // ==================== Private Methods ====================

    /**
     * التحقق من عدم تعيين صفوف لقوالب أخرى
     */
    private function validateGradeAssignments(array $gradeIds, int $academicYearId, ?int $excludeTemplateId = null): void
    {
        foreach ($gradeIds as $gradeId) {
            $existingAssignment = DB::table('grade_timetable_template')
                ->where('grade_id', $gradeId)
                ->where('academic_year_id', $academicYearId)
                ->when($excludeTemplateId, fn($q) => $q->where('template_id', '!=', $excludeTemplateId))
                ->first();

            if ($existingAssignment) {
                $existingTemplate = TimetableTemplate::find($existingAssignment->template_id);
                throw new GradeAlreadyAssignedException($gradeId, $existingAssignment->template_id, $existingTemplate);
            }
        }
    }

    /**
     * مزامنة الحصص
     */
    private function syncTimeSlots(TimetableTemplate $template, array $slots): void
    {
        // التحقق من عدم وجود جداول نشطة تستخدم القالب (إذا كان القالب نشط)
        if ($template->status === TemplateStatus::Active) {
            $activeTimetables = Timetable::whereHas('timeSlot', function($q) use ($template) {
                $q->where('template_id', $template->id);
            })->exists();

            if ($activeTimetables) {
                throw new TemplateNotEditableException(
                    $template->id,
                    $template->status,
                    'لا يمكن تعديل الحصص لأن هناك جداول نشطة تستخدم هذا القالب. يرجى أرشفة القالب أولاً.'
                );
            }
        }

        // حذف الحصص القديمة
        $template->timeSlots()->delete();

        // إنشاء الحصص الجديدة
        foreach ($slots as $slotData) {
            if ($slotData instanceof TimeSlotData) {
                $data = $slotData->toModelArray();
            } else {
                $data = $slotData;
            }

            $template->timeSlots()->create($data);
        }
    }

    /**
     * مزامنة الصفوف
     */
    private function syncGrades(TimetableTemplate $template, array $gradeIds, int $academicYearId): void
    {
        // حذف الروابط القديمة
        DB::table('grade_timetable_template')
            ->where('template_id', $template->id)
            ->delete();

        // إنشاء الروابط الجديدة
        foreach ($gradeIds as $gradeId) {
            DB::table('grade_timetable_template')->insert([
                'grade_id' => $gradeId,
                'template_id' => $template->id,
                'academic_year_id' => $academicYearId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

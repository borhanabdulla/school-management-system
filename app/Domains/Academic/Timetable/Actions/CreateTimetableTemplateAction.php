<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Actions;

use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use App\Domains\Academic\Timetable\Data\TimetableTemplateData;
use App\Domains\Academic\Timetable\Data\TimeSlotData;
use App\Domains\Academic\Timetable\Validators\TimetableGradeValidator;
use App\Domains\Academic\Timetable\Events\TimetableTemplateCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * CreateTimetableTemplateAction - إنشاء قالب جدول جديد
 * 
 * Action للكتابة فقط. مهمة واحدة: إنشاء القالب.
 * التحقق مفصول في TimetableGradeValidator.
 */
class CreateTimetableTemplateAction
{
    public function __construct(
        private TimetableGradeValidator $validator
    ) {
    }

    public function execute(TimetableTemplateData $data): TimetableTemplate
    {
        // التحقق (مفصول في Validator)
        $this->validator->validateGradeAssignments($data->gradeIds, $data->academicYearId);

        return DB::transaction(function () use ($data) {
            // إنشاء القالب
            $template = TimetableTemplate::create($data->toModelArray());

            // حفظ الحصص
            foreach ($data->slots as $slotData) {
                if ($slotData instanceof TimeSlotData) {
                    $slotArray = $slotData->toModelArray();
                } else {
                    // Fallback: Handle array or object with camelCase keys
                    $raw = is_array($slotData) ? $slotData : (array) $slotData;

                    $slotArray = [
                        'day_of_week' => $raw['day_of_week'] ?? $raw['dayOfWeek'] ?? 0,
                        'label' => $raw['label'] ?? '',
                        'order_index' => $raw['order_index'] ?? $raw['orderIndex'] ?? 0,
                        'start_time' => $raw['start_time'] ?? $raw['startTime'] ?? '00:00',
                        'end_time' => $raw['end_time'] ?? $raw['endTime'] ?? '00:00',
                        'type' => $raw['type'] ?? 'academic',
                        'is_attendance_checkpoint' => $raw['is_attendance_checkpoint'] ?? $raw['isAttendanceCheckpoint'] ?? false,
                    ];
                }

                $template->timeSlots()->create($slotArray);
            }

            // ربط الصفوف
            foreach ($data->gradeIds as $gradeId) {
                DB::table('grade_timetable_template')->insert([
                    'grade_id' => $gradeId,
                    'template_id' => $template->id,
                    'academic_year_id' => $data->academicYearId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Log::info('Timetable template created', ['id' => $template->id]);

            event(new TimetableTemplateCreated($template));

            return $template->fresh(['timeSlots', 'grades']);
        });
    }
}


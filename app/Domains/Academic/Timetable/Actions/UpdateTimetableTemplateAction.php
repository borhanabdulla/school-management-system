<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Actions;

use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use App\Domains\Academic\Timetable\Data\TimetableTemplateData;
use App\Domains\Academic\Timetable\Data\TimeSlotData;
use App\Domains\Academic\Timetable\Validators\TimetableGradeValidator;
use App\Domains\Academic\Timetable\Exceptions\TemplateNotEditableException;
use App\Domains\Academic\Timetable\Exceptions\CannotDeleteTimeSlotsInUseException;
use App\Domains\Academic\Timetable\Events\TimetableTemplateUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * UpdateTimetableTemplateAction - تحديث قالب جدول موجود
 * 
 * @security-critical ⚠️  PHASE 2: Added guard to prevent orphaning timetable entries
 * 
 * This action safely updates a timetable template while protecting data integrity.
 * 
 * Guard Added:
 * - Before deleting timeSlots, checks if they're used in timetables
 * - Throws CannotDeleteTimeSlotsInUseException if slots are referenced
 * 
 * @throws TemplateNotEditableException If template status is not editable
 * @throws CannotDeleteTimeSlotsInUseException If timeSlots are in use by timetables
 */
class UpdateTimetableTemplateAction
{
    public function __construct(
        private TimetableGradeValidator $validator
    ) {
    }

    public function execute(TimetableTemplate $template, TimetableTemplateData $data): TimetableTemplate
    {
        // Guard 1: Check if template is editable
        if (!$template->isEditable()) {
            throw new TemplateNotEditableException($template->id, $template->status);
        }

        // Validation (separated in Validator)
        $this->validator->validateGradeAssignments($data->gradeIds, $data->academicYearId, $template->id);

        return DB::transaction(function () use ($template, $data) {
            // Guard 2: Check if any timeSlots being deleted are used in timetables
            $this->guardAgainstDeletingTimeSlotsInUse($template);

            // Update template
            $template->update($data->toModelArray());

            // Update timeSlots
            $template->timeSlots()->delete();
            foreach ($data->slots as $slotData) {
                $slotArray = ($slotData instanceof TimeSlotData)
                    ? $slotData->toModelArray()
                    : $slotData;
                $template->timeSlots()->create($slotArray);
            }

            // Update grades
            DB::table('grade_timetable_template')
                ->where('template_id', $template->id)
                ->delete();

            foreach ($data->gradeIds as $gradeId) {
                DB::table('grade_timetable_template')->insert([
                    'grade_id' => $gradeId,
                    'template_id' => $template->id,
                    'academic_year_id' => $data->academicYearId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Log::info('Timetable template updated', ['id' => $template->id]);

            event(new TimetableTemplateUpdated($template));

            return $template->fresh(['timeSlots', 'grades']);
        });
    }

    /**
     * Guard against deleting timeSlots that are in use by timetables
     * 
     * This prevents orphaning timetable entries when a template is updated.
     * If timeSlots are in use, the user must first delete or reassign
     * the affected timetable entries.
     * 
     * @throws CannotDeleteTimeSlotsInUseException If timeSlots are referenced by timetables
     */
    private function guardAgainstDeletingTimeSlotsInUse(TimetableTemplate $template): void
    {
        // Get all current timeSlot IDs for this template
        $currentTimeSlotIds = $template->timeSlots()->pluck('id')->toArray();

        if (empty($currentTimeSlotIds)) {
            return; // No slots to protect
        }

        // Check if any of these timeSlots are used in timetables
        $usageCount = DB::table('timetables')
            ->whereIn('time_slot_id', $currentTimeSlotIds)
            ->count();

        if ($usageCount > 0) {
            // Find which timeSlots are in use
            $timeSlotsInUse = DB::table('timetables')
                ->whereIn('time_slot_id', $currentTimeSlotIds)
                ->distinct()
                ->pluck('time_slot_id')
                ->toArray();

            Log::warning('Attempted to delete timeSlots in use', [
                'template_id' => $template->id,
                'time_slot_ids' => $timeSlotsInUse,
                'usage_count' => $usageCount,
            ]);

            throw new CannotDeleteTimeSlotsInUseException(
                $timeSlotsInUse,
                $usageCount
            );
        }
    }
}

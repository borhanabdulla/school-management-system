<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Actions;

use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Timetable\Exceptions\TemplateNotEditableException;
use App\Domains\Academic\Timetable\Exceptions\CannotDeleteTimetableWithAttendanceException;
use App\Domains\Academic\Timetable\Enums\TemplateStatus;
use App\Domains\Academic\Timetable\Actions\DeleteTimetableEntryAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * DeleteTimetableTemplateAction - حذف قالب جدول
 *
 * @security-critical ⚠️  PHASE 3: Added guard to prevent orphaning attendance records
 *
 * This action safely deletes a timetable template while protecting data integrity.
 *
 * Guard Added:
 * - Before deleting linked timetable entries, checks for attendance records
 * - Throws CannotDeleteTimetableWithAttendanceException if attendance exists
 *
 * @throws TemplateNotEditableException If template is in Active status
 * @throws CannotDeleteTimetableWithAttendanceException If timetable entries have attendance
 */
class DeleteTimetableTemplateAction
{
    public function execute(TimetableTemplate $template): void
    {
        if ($template->status === TemplateStatus::Active) {
            throw new TemplateNotEditableException(
                $template->id,
                $template->status,
                "لا يمكن حذف قالب نشط. قم بأرشفته أولاً."
            );
        }

        DB::transaction(function () use ($template) {
            // 1. Get all slots of this template
            $slotIds = $template->timeSlots()->pluck('id');

            if ($slotIds->isNotEmpty()) {
                // Guard: Check for attendance records before deletion
                $this->guardAgainstTimetableWithAttendance($slotIds->toArray());

                // ✅ PR-2: Delete linked Timetable entries using centralized action
                $timetablesToDelete = Timetable::whereIn('time_slot_id', $slotIds)
                    ->where('term_id', $template->term_id) // ✅ PR-2: Add term scope
                    ->get();

                foreach ($timetablesToDelete as $timetable) {
                    app(DeleteTimetableEntryAction::class)->execute($timetable->id);
                }
            }

            // 3. Delete the template (TimeSlots will be deleted via cascade if set in DB)
            $template->delete();

            Log::info('Timetable template deleted', ['id' => $template->id]);
        });
    }

    /**
     * Guard against deleting timetable entries with attendance records
     *
     * ✅ PR-2: Uses the centralized DeleteTimetableEntryAction instead of direct delete
     *
     * @param array $slotIds Time slot IDs to check
     * @throws CannotDeleteTimetableWithAttendanceException
     */
    private function guardAgainstTimetableWithAttendance(array $slotIds): void
    {
        $timetableEntries = Timetable::whereIn('time_slot_id', $slotIds)->get();

        $totalAttendance = 0;
        $affectedCount = 0;

        foreach ($timetableEntries as $entry) {
            // ✅ PR-2: Use the relationship defined in PR-1
            $attendanceCount = $entry->attendances()->count();

            if ($attendanceCount > 0) {
                $totalAttendance += $attendanceCount;
                $affectedCount++;
            }
        }

        if ($totalAttendance > 0) {
            throw CannotDeleteTimetableWithAttendanceException::forMultipleTimetables(
                $affectedCount,
                $totalAttendance
            );
        }
    }
}

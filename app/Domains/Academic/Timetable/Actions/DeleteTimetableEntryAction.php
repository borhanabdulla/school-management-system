<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Actions;

use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Timetable\Exceptions\CannotDeleteTimetableWithAttendanceException;
use App\Domains\Academic\Services\AcademicWriteGuard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * DeleteTimetableEntryAction - حذف حصة جدول واحدة
 *
 * @security-critical ✅ PR-2: Centralized deletion with guards
 *
 * This action safely deletes a single timetable entry while protecting data integrity.
 *
 * Principles:
 * - Domain Invariants in One Place: All deletion logic in one action
 * - No Query Builder Bypass: Uses Model::delete() to trigger Traits/Observers
 *
 * @throws CannotDeleteTimetableWithAttendanceException If the entry has attendance records
 */
class DeleteTimetableEntryAction
{
    /**
     * حذف حصة جدول مع حماية من الـ orphan records
     *
     * @param int $timetableId معرف الجدول المراد حذفه
     * @throws CannotDeleteTimetableWithAttendanceException عند وجود حضور مرتبط
     */
    public function execute(int $timetableId): void
    {
        DB::transaction(function () use ($timetableId) {
            $timetable = Timetable::find($timetableId);

            if (!$timetable) {
                Log::warning("Timetable entry not found for deletion", ['timetable_id' => $timetableId]);
                return;
            }

            if ($timetable->term_id) {
                app(AcademicWriteGuard::class)->assertTermNotCompleted($timetable->term_id);
            } else {
                $timetable->loadMissing('classSection');
                if ($timetable->classSection?->academic_year_id) {
                    app(AcademicWriteGuard::class)->assertYearNotClosed($timetable->classSection->academic_year_id);
                }
            }

            // Guard: Check protected relations (uses the relationship defined in PR-1)
            // Note: HandlesSafeDelete will run automatically after model delete
            // Manual check here for custom Domain Exception message
            $attendanceCount = $timetable->attendances()->count();

            if ($attendanceCount > 0) {
                throw CannotDeleteTimetableWithAttendanceException::forTimetable(
                    $timetable,
                    $attendanceCount
                );
            }

            // Delete via model - triggers HandlesSafeDelete + Observers + Events
            $timetable->delete();

            Log::info('Timetable entry deleted', [
                'timetable_id' => $timetableId,
                'class_section_id' => $timetable->class_section_id,
                'time_slot_id' => $timetable->time_slot_id,
                'term_id' => $timetable->term_id,
            ]);
        });
    }
}

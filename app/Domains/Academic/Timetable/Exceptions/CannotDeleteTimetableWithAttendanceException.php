<?php

declare(strict_types=1);

namespace App\Domains\Academic\Timetable\Exceptions;

use Exception;
use App\Domains\Academic\Timetable\Models\Timetable;

/**
 * يُطرح عند محاولة حذف حصص جدول تحتوي على سجلات حضور
 * 
 * This exception is thrown when attempting to delete timetable entries
 * that have associated attendance records, preventing data orphaning.
 */
class CannotDeleteTimetableWithAttendanceException extends Exception
{
    protected int $timetableId;
    protected int $attendanceCount;

    /**
     * @param int $timetableId ID of the timetable entry
     * @param int $attendanceCount Number of attendance records
     * @param string|null $customMessage
     */
    public function __construct(
        int $timetableId,
        int $attendanceCount,
        ?string $customMessage = null
    ) {
        $this->timetableId = $timetableId;
        $this->attendanceCount = $attendanceCount;

        if (!$customMessage) {
            $customMessage = "لا يمكن حذف حصة الجدول لأنها تحتوي على {$attendanceCount} سجل حضور. يرجى حذف سجلات الحضور أولاً أو contact الدعم.";
        }

        parent::__construct($customMessage);
    }

    /**
     * Get the timetable ID
     */
    public function getTimetableId(): int
    {
        return $this->timetableId;
    }

    /**
     * Get the number of attendance records
     */
    public function getAttendanceCount(): int
    {
        return $this->attendanceCount;
    }

    /**
     * Create exception for a single timetable entry
     */
    public static function forTimetable(Timetable $timetable, int $attendanceCount): self
    {
        $subjectName = $timetable->courseOffering?->subject?->name ?? 'غير معروف';
        $className = $timetable->classSection?->name ?? 'غير معروف';
        
        return new self(
            $timetable->id,
            $attendanceCount,
            "لا يمكن حذف حصة {$subjectName} للصف {$className} لأنها تحتوي على {$attendanceCount} سجل حضور. قم بحذف سجلات الحضور أولاً."
        );
    }

    /**
     * Create exception for multiple timetable entries
     */
    public static function forMultipleTimetables(int $count, int $totalAttendance): self
    {
        return new self(
            0, // Multiple entries
            $totalAttendance,
            "لا يمكن حذف {$count} حصص جدول لأن بها {$totalAttendance} سجل حضور. قم بحذف سجلات الحضور أولاً."
        );
    }
}

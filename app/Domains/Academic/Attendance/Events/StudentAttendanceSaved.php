<?php

namespace App\Domains\Academic\Attendance\Events;

use App\Domains\Academic\Timetable\Models\Timetable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentAttendanceSaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $timetable;
    public $date;
    public $absentStudentIds;

    /**
     * Create a new event instance.
     */
    public function __construct(Timetable $timetable, string $date, array $absentStudentIds)
    {
        $this->timetable = $timetable;
        $this->date = $date;
        $this->absentStudentIds = $absentStudentIds;
    }
}

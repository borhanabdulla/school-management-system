<?php

namespace App\Domains\Academic\Attendance\Events;

use App\Domains\Academic\Timetable\Models\Timetable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceBatchSaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Timetable $timetable
     * @param string $date
     * @param array $studentIds All students who had attendance recorded (present or absent)
     */
    public function __construct(
        public Timetable $timetable,
        public string $date,
        public array $studentIds
    ) {
    }
}

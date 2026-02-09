<?php

namespace App\Domains\Academic\Student\Events;

use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentAssignedToClass
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Student $student,
        public ClassSection $section
    ) {
    }
}

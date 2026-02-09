<?php

declare(strict_types=1);

namespace App\Domains\Academic\Student\Events;

use App\Domains\Academic\Student\Models\Student;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Student $student
    ) {
    }
}

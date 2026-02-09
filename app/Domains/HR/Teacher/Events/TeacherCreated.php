<?php

namespace App\Domains\HR\Teacher\Events;

use App\Domains\HR\Teacher\Models\Teacher;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeacherCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Teacher $teacher)
    {
    }
}

<?php

namespace App\Domains\HR\Substitution\Events;

use App\Domains\HR\Teacher\Models\Teacher;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * TeacherAbsentWithClasses - حدث غياب معلم لديه حصص
 * 
 * يُطلق عندما يُسجّل غياب معلم لديه حصص في ذلك اليوم
 */
class TeacherAbsentWithClasses
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Teacher $teacher,
        public string $date
    ) {
    }
}

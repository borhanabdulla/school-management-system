<?php

namespace App\Domains\Academic\Homework\Enums;

enum SubmissionStatus: string
{
    case PENDING = 'pending';
    case SUBMITTED = 'submitted';
    case LATE = 'late';
    case GRADED = 'graded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'قيد الانتظار',
            self::SUBMITTED => 'تم التسليم',
            self::LATE => 'متأخر',
            self::GRADED => 'تم الرصد',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::SUBMITTED => 'blue',
            self::LATE => 'red',
            self::GRADED => 'green',
        };
    }
}

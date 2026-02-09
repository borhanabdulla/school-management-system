<?php

namespace App\Domains\Academic\Attendance\Enums;

enum AttendanceMode: string
{
    case Daily = 'daily_only';
    case PerPeriod = 'per_period';
    case Checkpoints = 'checkpoints';

    public function label(): string
    {
        return match ($this) {
            self::Daily => 'يومي (مرة واحدة صباحاً)',
            self::PerPeriod => 'كل حصة (دقيق جداً)',
            self::Checkpoints => 'نقاط تفتيش (مخصص)',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Daily => 'يتم رصد الغياب مرة واحدة في اليوم، عادة في الطابور الصباحي أو الحصة الأولى.',
            self::PerPeriod => 'يتم رصد الغياب لكل حصة دراسية بشكل مستقل. هذا الخيار هو الأكثر دقة ولكنه يتطلب جهداً أكبر.',
            self::Checkpoints => 'يتم تحديد حصص معينة (مثل الحصة الأولى والرابعة) كنقاط لرصد الغياب.',
        };
    }
}

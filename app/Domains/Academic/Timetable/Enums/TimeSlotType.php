<?php

namespace App\Domains\Academic\Timetable\Enums;

enum TimeSlotType: string
{
    case Academic = 'academic';
    case Break = 'break';
    case Assembly = 'assembly';
    case Activity = 'activity';
    case Prayer = 'prayer';

    public function label(): string
    {
        return match ($this) {
            self::Academic => 'حصة دراسية',
            self::Break => 'استراحة',
            self::Assembly => 'طابور/اجتماع',
            self::Activity => 'نشاط/رياضة',
            self::Prayer => 'صلاة',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Academic => 'indigo',
            self::Break => 'amber',
            self::Assembly => 'emerald',
            self::Activity => 'purple',
            self::Prayer => 'teal',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Academic => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
            self::Break => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            self::Assembly => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
            self::Activity => 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            self::Prayer => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
        };
    }

    /**
     * هل هذا النوع قابل للتعيين (يُسند له معلم ومادة)؟
     */
    public function isAssignable(): bool
    {
        return in_array($this, [self::Academic, self::Activity]);
    }

    /**
     * هل هذا النوع يحتسب ضمن حصص المعلم؟
     */
    public function countsForTeacherLoad(): bool
    {
        return $this === self::Academic;
    }
}

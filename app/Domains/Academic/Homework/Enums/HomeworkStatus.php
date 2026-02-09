<?php

namespace App\Domains\Academic\Homework\Enums;

enum HomeworkStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'مسودة',
            self::PUBLISHED => 'منشور',
            self::ARCHIVED => 'مؤرشف',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::PUBLISHED => 'green',
            self::ARCHIVED => 'yellow',
        };
    }
}

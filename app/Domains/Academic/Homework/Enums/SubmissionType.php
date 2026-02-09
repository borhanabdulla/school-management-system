<?php

namespace App\Domains\Academic\Homework\Enums;

enum SubmissionType: string
{
    case ONLINE = 'online';
    case OFFLINE = 'offline';

    public function label(): string
    {
        return match ($this) {
            self::ONLINE => 'إلكتروني (رفع ملف)',
            self::OFFLINE => 'ورقي / في الفصل',
        };
    }
}

<?php

namespace App\Domains\Academic\Promotion\Enums;

enum PromotionType: string
{
    case Promoted = 'promoted';
    case Repeated = 'repeated';
    case Graduated = 'graduated';
    case Transferred = 'transferred';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::Promoted => 'منقول',
            self::Repeated => 'معيد',
            self::Graduated => 'خريج',
            self::Transferred => 'منقول خارجياً',
            self::Withdrawn => 'منسحب',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Promoted => 'success',
            self::Repeated => 'danger',
            self::Graduated => 'info',
            self::Transferred => 'warning',
            self::Withdrawn => 'secondary',
        };
    }
}

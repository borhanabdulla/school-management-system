<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Events;

use App\Domains\Academic\Grading\Models\MonthlyGrade;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * MonthlyGradeSaved - حدث حفظ درجة شهرية
 * 
 * يُطلق عند حفظ أو تحديث درجة شهرية لتفعيل المزامنة التلقائية
 */
class MonthlyGradeSaved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public MonthlyGrade $monthlyGrade
    ) {
    }
}

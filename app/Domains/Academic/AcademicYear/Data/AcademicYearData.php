<?php

namespace App\Domains\Academic\AcademicYear\Data;

use App\Infrastructure\Data\BaseData;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use Carbon\Carbon;

class AcademicYearData extends BaseData
{
    public function __construct(
        public readonly string $name,
        public readonly Carbon $start_date,
        public readonly Carbon $end_date,
        public readonly ?AcademicYearStatus $status = null,
        public readonly array $terms = [],
    ) {
    }
}
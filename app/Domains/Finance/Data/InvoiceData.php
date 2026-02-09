<?php

declare(strict_types=1);

namespace App\Domains\Finance\Data;

use App\Infrastructure\Data\BaseData;

/**
 * InvoiceData - DTO لإنشاء فاتورة
 * 
 * @example
 * $data = InvoiceData::fromArray([
 *     'student_id' => 1,
 *     'academic_year_id' => 1,
 *     'grade_id' => 1,
 * ]);
 */
class InvoiceData extends BaseData
{
    public function __construct(
        public readonly int $studentId,
        public readonly int $academicYearId,
        public readonly int $gradeId,
        public readonly ?string $dueDate = null,
        public readonly bool $generateItems = true,
    ) {
    }
}

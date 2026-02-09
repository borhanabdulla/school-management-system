<?php

declare(strict_types=1);

namespace App\Domains\Finance\Data;

use App\Infrastructure\Data\BaseData;
use App\Domains\Finance\Enums\PaymentMethod;

/**
 * PaymentData - DTO لتسجيل دفعة مالية
 * 
 * @example
 * $data = PaymentData::fromArray([
 *     'invoice_id' => 1,
 *     'guardian_id' => 5,
 *     'amount' => 500.00,
 *     'method' => PaymentMethod::Cash,
 * ]);
 */
class PaymentData extends BaseData
{
    public function __construct(
        public readonly int $invoiceId,
        public readonly int $guardianId,
        public readonly float $amount,
        public readonly PaymentMethod $method,
        public readonly ?string $transactionReference = null,
        public readonly ?string $notes = null,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Finance\Data;

class DiscountSuggestion
{
    /**
     * @param string $policyCode رمز السياسة (للترجمة والتمييز)
     * @param string $description وصف سبب الاستحقاق
     * @param int|null $suggestedDiscountId معرف الخصم المقترح (من جدول discounts)
     * @param array $eligibleItemIds مصفوفة معرفات invoice_items المستحقة
     * @param float $suggestedAmount القيمة المقترحة للخصم
     */
    public function __construct(
        public string $policyCode,
        public string $description,
        public ?int $suggestedDiscountId,
        public array $eligibleItemIds,
        public float $suggestedAmount
    ) {
    }
}

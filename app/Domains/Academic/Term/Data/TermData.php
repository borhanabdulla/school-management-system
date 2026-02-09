<?php

namespace App\Domains\Academic\Term\Data;

use App\Domains\Academic\Term\Enums\TermStatus; // 1. استيراد الـ Enum

// 2. نجعل الكلاس readonly (ميزة في PHP 8.2+)
readonly class TermData
{
    public function __construct(
        public int $academic_year_id,
        public string $name,
        public string $start_date,
        public string $end_date,
        public int $order_index,
        // 3. نستخدم النوع القوي Enum بدلاً من string
        public TermStatus $status,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            academic_year_id: (int) $data['academic_year_id'],
            name: $data['name'],
            start_date: $data['start_date'],
            end_date: $data['end_date'],
            order_index: (int) $data['order_index'],

            // 4. نقبل Enum مباشر أو قيمة نصية من الفورم.
            status: $data['status'] instanceof TermStatus
                ? $data['status']
                : TermStatus::from($data['status']),
        );
    }

    public function toArray(): array
    {
        return [
            'academic_year_id' => $this->academic_year_id,
            'name' => $this->name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'order_index' => $this->order_index,

            // 5. عند التحويل لمصفوفة (للداتابيز)، نرجع القيمة النصية
            'status' => $this->status->value,
        ];
    }
}

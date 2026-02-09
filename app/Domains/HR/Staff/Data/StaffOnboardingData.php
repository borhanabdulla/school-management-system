<?php

namespace App\Domains\HR\Staff\Data;

use App\Domains\HR\Enums\StaffRole;

/**
 * StaffOnboardingData - بيانات إضافة موظف جديد
 */
class StaffOnboardingData
{
    public function __construct(
        // البيانات الشخصية
        public string $first_name,
        public string $last_name,
        public string $email,
        public ?string $phone = null,
        public string $joining_date,

        // بيانات الوظيفة
        public StaffRole $role,
        public string $job_title,
        public ?int $work_shift_id = null,
        public string $employment_type = 'full_time',

        // بيانات المعلم (اختيارية)
        public ?string $specialization = null,
        public ?int $max_weekly_classes = null,

        // بيانات الحساب
        public bool $create_account = true,
        public ?string $password = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            first_name: $data['first_name'],
            last_name: $data['last_name'],
            email: $data['email'],
            phone: $data['phone'] ?? null,
            joining_date: $data['joining_date'],
            role: StaffRole::from($data['role']),
            job_title: $data['job_title'],
            work_shift_id: $data['work_shift_id'] ?? null,
            employment_type: $data['employment_type'] ?? 'full_time',
            specialization: $data['specialization'] ?? null,
            max_weekly_classes: $data['max_weekly_classes'] ?? null,
            create_account: $data['create_account'] ?? true,
            password: $data['password'] ?? null,
        );
    }
}

<?php

namespace App\Livewire\Forms\HR;

use Livewire\Form;
use Livewire\Attributes\Validate;
use App\Domains\HR\Enums\StaffRole;

class StaffCreateForm extends Form
{
    // ============================================
    // البيانات الشخصية
    // ============================================
    #[Validate('required|string|min:2|max:50')]
    public string $first_name = '';

    #[Validate('required|string|min:2|max:50')]
    public string $last_name = '';

    #[Validate('required|email|unique:users,email')]
    public string $email = '';

    #[Validate('nullable|string|min:8|max:15')]
    public ?string $phone = null;

    // ============================================
    // بيانات الوظيفة
    // ============================================
    #[Validate('required|string')]
    public string $role = '';

    #[Validate('required|string|max:100')]
    public string $job_title = '';

    #[Validate('nullable|exists:work_shifts,id')]
    public ?int $work_shift_id = null;

    #[Validate('required|in:full_time,part_time,contractor')]
    public string $employment_type = 'full_time';

    #[Validate('required|date')]
    public string $joining_date = '';

    // ============================================
    // بيانات المعلم (تظهر فقط إذا كان الدور معلم)
    // ============================================
    #[Validate('nullable|string|max:100')]
    public ?string $specialization = null;

    #[Validate('nullable|integer|min:1|max:40')]
    public ?int $max_weekly_classes = 24;

    // ============================================
    // إعدادات الحساب
    // ============================================
    public bool $create_account = true;

    public function rules()
    {
        $rules = [
            'first_name' => 'required|string|min:2|max:50',
            'last_name' => 'required|string|min:2|max:50',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|min:8|max:15',
            'role' => 'required|string',
            'job_title' => 'required|string|max:100',
            'work_shift_id' => 'nullable|exists:work_shifts,id',
            'employment_type' => 'required|in:full_time,part_time,contractor',
            'joining_date' => 'required|date',
        ];

        // إضافة قواعد المعلم إذا كان الدور معلم
        if ($this->role === StaffRole::Teacher->value) {
            $rules['specialization'] = 'required|string|max:100';
            $rules['max_weekly_classes'] = 'required|integer|min:1|max:40';
        }

        return $rules;
    }

    public function prepareData(): \App\Data\Staff\StaffOnboardingData
    {
        $this->validate();

        return new \App\Data\Staff\StaffOnboardingData(
            first_name: $this->first_name,
            last_name: $this->last_name,
            email: $this->email,
            phone: $this->phone,
            joining_date: $this->joining_date,
            role: StaffRole::from($this->role),
            job_title: $this->job_title,
            work_shift_id: $this->work_shift_id,
            employment_type: $this->employment_type,
            specialization: $this->specialization,
            max_weekly_classes: $this->max_weekly_classes,
            create_account: $this->create_account,
        );
    }
}

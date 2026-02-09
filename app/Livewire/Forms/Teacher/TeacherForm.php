<?php

namespace App\Livewire\Forms\Teacher;

use Livewire\Form;
use Livewire\Attributes\Validate;
use Illuminate\Validation\Rule;

class TeacherForm extends Form
{
    use \Livewire\WithFileUploads;

    // البيانات الشخصية (Staff)
    #[Validate('required|string|min:2|max:50')]
    public $first_name = '';

    #[Validate('required|string|min:2|max:50')]
    public $last_name = '';

    #[Validate('required|email|unique:users,email')]
    public $email = '';

    #[Validate('nullable|string|min:8|max:15')]
    public $phone = '';

    // بيانات الحساب (User)
    #[Validate('required|string|min:8')]
    public $password = '';

    // البيانات الوظيفية (Teacher)
    #[Validate('required|date')]
    public $hire_date = '';

    #[Validate('nullable|string|max:100')]
    public $specialization = ''; // التخصص (نص حر أو من القائمة)

    #[Validate('required|integer|min:1|max:50')]
    public $max_weekly_classes = 24; // النصاب الأسبوعي

    #[Validate('nullable|image|max:2048')]
    public $photo;

    // دالة لاستخراج بيانات المستخدم فقط
    public function userData(): array
    {
        return [
            'name' => $this->first_name . ' ' . $this->last_name,
            'email' => $this->email,
            'password' => $this->password, // سيتم تشفيره في الـ Action
        ];
    }

    // دالة لاستخراج بيانات الموظف
    public function staffData(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
        ];
    }
    // تحويل إلى DTO
    public function toDto(): \App\Domains\HR\Teacher\Data\TeacherOnboardingData
    {
        return new \App\Domains\HR\Teacher\Data\TeacherOnboardingData(
            first_name: $this->first_name,
            last_name: $this->last_name,
            email: $this->email,
            phone: $this->phone,
            password: $this->password,
            hire_date: $this->hire_date,
            specialization: $this->specialization,
            max_weekly_classes: $this->max_weekly_classes,
            photo: $this->photo
        );
    }
}
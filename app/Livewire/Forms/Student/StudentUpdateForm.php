<?php

namespace App\Livewire\Forms\Student;

use Livewire\Form;
use Livewire\Attributes\Validate;
use Illuminate\Validation\Rule;

class StudentUpdateForm extends Form
{
    public ?int $id = null;

    #[Validate]
    public $first_name_ar = '';

    #[Validate]
    public $family_name_ar = '';

    #[Validate]
    public $date_of_birth = '';

    #[Validate]
    public $gender = '';

    #[Validate]
    public $nationality_id = '';

    #[Validate]
    public $blood_type = '';

    #[Validate]
    public $national_id = '';

    public function rules()
    {
        return [
            'first_name_ar' => ['required', 'string', 'max:255', 'regex:/^[\p{Arabic}\s]+$/u'],
            'family_name_ar' => ['required', 'string', 'max:255', 'regex:/^[\p{Arabic}\s]+$/u'],
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'nationality_id' => 'nullable|exists:countries,id',
            'blood_type' => 'nullable|string|max:3',
            'national_id' => ['nullable', 'max:20', Rule::unique('students', 'national_id')->ignore($this->id)],
        ];
    }

    public function messages()
    {
        return [
            'first_name_ar.required' => 'يرجى إدخال الاسم الأول للطالب باللغة العربية.',
            'first_name_ar.string' => 'الاسم الأول يجب أن يكون نصاً.',
            'first_name_ar.max' => 'الاسم الأول طويل جداً (الحد الأقصى 255 حرف).',
            'first_name_ar.regex' => 'الاسم الأول يجب أن يحتوي على أحرف عربية فقط.',
            
            'family_name_ar.required' => 'يرجى إدخال اسم العائلة للطالب باللغة العربية.',
            'family_name_ar.string' => 'اسم العائلة يجب أن يكون نصاً.',
            'family_name_ar.max' => 'اسم العائلة طويل جداً (الحد الأقصى 255 حرف).',
            'family_name_ar.regex' => 'اسم العائلة يجب أن يحتوي على أحرف عربية فقط.',
            
            'date_of_birth.required' => 'تاريخ الميلاد مطلوب.',
            'date_of_birth.date' => 'صيغة تاريخ الميلاد غير صحيحة.',
            'date_of_birth.before' => 'تاريخ الميلاد يجب أن يكون تاريخاً سابقاً لليوم.',
            
            'gender.required' => 'يرجى تحديد جنس الطالب.',
            'gender.in' => 'الجنس المختار غير صالح.',
            
            'nationality_id.exists' => 'الجنسية المختارة غير موجودة في النظام.',
            
            'blood_type.string' => 'فصيلة الدم غير صالحة.',
            'blood_type.max' => 'فصيلة الدم طويلة جداً.',
            
            'national_id.unique' => 'الرقم القومي هذا مسجل بالفعل لطالب آخر.',
            'national_id.max' => 'الرقم القومي يجب ألا يتجاوز 20 رقماً.',
        ];
    }

    public function setStudent($student)
    {
        $this->id = $student->id;
        $this->first_name_ar = $student->first_name_ar;
        $this->family_name_ar = $student->family_name_ar;
        $this->date_of_birth = $student->date_of_birth?->format('Y-m-d');
        $this->gender = $student->gender->value;
        $this->nationality_id = $student->nationality_id;
        $this->blood_type = $student->blood_type;
        $this->national_id = $student->national_id;
    }
}

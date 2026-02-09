<?php

namespace App\Livewire\Forms\Teacher;

use Livewire\Form;
use Livewire\Attributes\Rule;
class TeacherFilterForm extends Form
{
   // البحث العام
    public $search = '';
    
    // الفلاتر الهيكلية
    public $academic_year_id = '';
    public $grade_id = '';
    public $class_section_id = ''; // الفلتر الدقيق
    
    // فلتر المادة
    public $subject_name = '';

    public function toFiltersArray(): array
    {
        return [
            'search' => trim($this->search),
            'academic_year_id' => $this->academic_year_id ?: null,
            'grade_id' => $this->grade_id ?: null,
            'class_section_id' => $this->class_section_id ?: null,
            'subject_name' => trim($this->subject_name),
        ];
    }
}
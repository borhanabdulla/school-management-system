<?php

namespace App\Livewire\Forms\Academic;

use Livewire\Form;
use Livewire\Attributes\Validate;
use App\Domains\Academic\ClassSection\Enums\SectionGenderType;
use Illuminate\Validation\Rule;

class ClassSectionForm extends Form
{
    public ?int $id = null;

    #[Validate]
    public $academic_year_id = '';

    #[Validate]
    public $grade_id = '';

    #[Validate]
    public $name = '';

    #[Validate]
    public $max_capacity = 30;

    #[Validate]
    public $gender_type = 'mixed';

    #[Validate]
    public $is_active = true;

    public function rules()
    {
        return [
            'academic_year_id' => 'required|exists:academic_years,id',
            'grade_id' => 'required|exists:grades,id',
            'name' => 'required|string|max:50',
            'max_capacity' => 'required|integer|min:1',
            'gender_type' => ['required', Rule::enum(SectionGenderType::class)],
            'is_active' => 'boolean',
        ];
    }

    public function setSection($section)
    {
        $this->id = $section->id;
        $this->academic_year_id = $section->academic_year_id;
        $this->grade_id = $section->grade_id;
        $this->name = $section->name;
        $this->max_capacity = $section->max_capacity;
        $this->gender_type = $section->gender_type->value;
        $this->is_active = (bool) $section->is_active;
    }
}

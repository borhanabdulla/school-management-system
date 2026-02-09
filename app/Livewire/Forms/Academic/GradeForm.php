<?php

namespace App\Livewire\Forms\Academic;

use Livewire\Form;
use Livewire\Attributes\Validate;
use Illuminate\Validation\Rule;

class GradeForm extends Form
{
    public ?int $id = null;

    #[Validate]
    public $educational_stage_id = '';

    #[Validate]
    public $name = '';

    #[Validate]
    public $level_order = '';

    #[Validate]
    public $next_grade_id = null;

    public function rules()
    {
        return [
            'educational_stage_id' => 'required|exists:educational_stages,id',
            'name' => 'required|string',
            'level_order' => 'required|integer|min:1',
            'next_grade_id' => ['nullable', 'exists:grades,id', function ($attr, $val, $fail) {
                if ($val == $this->id) $fail('لا يمكن اختيار الصف نفسه كصف تالي.');
            }],
        ];
    }

    public function setGrade($grade)
    {
        $this->id = $grade->id;
        $this->educational_stage_id = $grade->educational_stage_id;
        $this->name = $grade->name;
        $this->level_order = $grade->level_order;
        $this->next_grade_id = $grade->next_grade_id;
    }
}

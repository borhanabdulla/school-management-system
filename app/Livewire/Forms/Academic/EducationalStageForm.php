<?php

namespace App\Livewire\Forms\Academic;

use Livewire\Form;
use Livewire\Attributes\Validate;
use Illuminate\Validation\Rule;

class EducationalStageForm extends Form
{
    public ?int $id = null;

    #[Validate]
    public $name = '';

    #[Validate]
    public $rank = '';

    #[Validate]
    public $min_passing_percentage = 50;// متغير من اجل نظام الدرجات    

    #[Validate]
    public $grading_system = 'standard';

    public function rules()
    {
        return [
            'name' => ['required', 'string', Rule::unique('educational_stages', 'name')->ignore($this->id)],
            'rank' => ['required', 'integer', Rule::unique('educational_stages', 'rank')->ignore($this->id)],
            'min_passing_percentage' => 'required|numeric|min:0|max:100',
            'grading_system' => 'required|in:standard,gpa',
        ];
    }

    public function setStage($stage)
    {
        $this->id = $stage->id;
        $this->name = $stage->name;
        $this->rank = $stage->rank;
        $this->min_passing_percentage = $stage->min_passing_percentage;
        $this->grading_system = $stage->grading_system;
    }
}

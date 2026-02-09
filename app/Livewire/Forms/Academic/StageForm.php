<?php

namespace App\Livewire\Forms\Academic;

use Livewire\Form;
use Livewire\Attributes\Validate;
use App\Models\EducationalStage;
use Illuminate\Validation\Rule;

class StageForm extends Form
{
    public ?EducationalStage $stage = null;

    public $name = '';
    public $rank = '';
    public $min_passing_percentage = 50;
    public $grading_system = 'standard';

    public function setStage(EducationalStage $stage)
    {
        $this->stage = $stage;
        $this->name = $stage->name;
        $this->rank = $stage->rank;
        $this->min_passing_percentage = $stage->min_passing_percentage;
        $this->grading_system = $stage->grading_system;
    }

    public function rules()
    {
        return [
            'name' => [
                'required', 
                'string', 
                Rule::unique('educational_stages', 'name')->ignore($this->stage?->id)
            ],
            'rank' => [
                'required', 
                'integer', 
                Rule::unique('educational_stages', 'rank')->ignore($this->stage?->id)
            ],
            'min_passing_percentage' => 'required|numeric|min:0|max:100',
            'grading_system' => 'required|in:standard,gpa',
        ];
    }
}

<?php

namespace App\Livewire\Forms\Academic;

use Livewire\Form;
use Livewire\Attributes\Validate;
use Illuminate\Validation\Rule;

class SubjectForm extends Form
{
    public ?int $id = null;

    public $name = '';

    public $code = '';

    public $type = 'theory';

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('subjects', 'name')->ignore($this->id)],
            'code' => ['nullable', 'string', 'max:20', Rule::unique('subjects', 'code')->ignore($this->id)],
            'type' => 'required|in:theory,practical,both',
        ];
    }

    public function setSubject($subject)
    {
        $this->id = $subject->id;
        $this->name = $subject->name;
        $this->code = $subject->code;
        $this->type = $subject->type;
    }
}

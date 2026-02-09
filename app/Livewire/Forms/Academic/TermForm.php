<?php

namespace App\Livewire\Forms\Academic;

use Livewire\Form;
use Livewire\Attributes\Validate;
use App\Domains\Academic\Term\Enums\TermStatus;
use Illuminate\Validation\Rule;

class TermForm extends Form
{
    public ?int $id = null;

    #[Validate]
    public $academic_year_id = '';

    #[Validate]
    public $name = '';

    #[Validate]
    public $start_date = '';

    #[Validate]
    public $end_date = '';

    #[Validate]
    public $order_index = '';

    #[Validate]
    public $status = '';

    public function rules()
    {
        return [
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('terms')->where(function ($query) {
                    return $query->where('academic_year_id', $this->academic_year_id);
                })->ignore($this->id),
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'order_index' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('terms')->where(function ($query) {
                    return $query->where('academic_year_id', $this->academic_year_id);
                })->ignore($this->id),
            ],
            'status' => ['required', Rule::enum(TermStatus::class)],
        ];
    }

    public function setTerm($term)
    {
        $this->id = $term->id;
        $this->academic_year_id = $term->academic_year_id;
        $this->name = $term->name;
        $this->start_date = $term->start_date?->format('Y-m-d');
        $this->end_date = $term->end_date?->format('Y-m-d');
        $this->order_index = $term->order_index;
        $this->status = $term->status->value;
    }
}

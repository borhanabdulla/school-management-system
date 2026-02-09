<?php

namespace App\Livewire\Forms\CourseOffering;

use Livewire\Form;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Actions\CourseOffering\AssignTeacherAction;

class AssignTeacherForm extends Form
{
    public ?CourseOffering $courseOffering = null;

    public $teacher_id = '';

    public function setCourseOffering(CourseOffering $courseOffering)
    {
        $this->courseOffering = $courseOffering;
        $this->teacher_id = $courseOffering->teacher_id;
    }

    public function rules()
    {
        return ['teacher_id' => 'nullable|exists:teachers,id'];
    }

    public function save()
    {
        $this->validate();
        app(AssignTeacherAction::class)->execute($this->courseOffering->id, $this->teacher_id ?: null);
        $this->dispatch('teacher-assigned');
    }
}
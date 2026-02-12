<?php

namespace App\Livewire\Teacher\Homework;

use App\Domains\Academic\Homework\Enums\HomeworkStatus;
use App\Domains\Academic\Homework\Enums\SubmissionType;
use App\Domains\Academic\Grading\Models\Assessment;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Homework\Models\Homework;
use App\Domains\Academic\Homework\Services\HomeworkService;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class HomeworkManager extends Component
{
    use WithFileUploads;

    #[Locked]
    public $courseOfferingId;

    public $showModal = false;
    public $isEditing = false;

    #[Locked]
    public $homeworkId;

    // Form fields
    public $title;
    public $description;
    public $submission_type;
    public $status;
    public $due_date;
    public $allow_late = false;
    public $max_score = 10;
    public $assessment_id;
    public $attachment;
    public $existingAttachment;

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'submission_type' => 'required',
        'status' => 'required',
        'due_date' => 'nullable|date',
        'allow_late' => 'boolean',
        'max_score' => 'required|numeric|min:0',
        'assessment_id' => 'nullable|exists:assessments,id',
        'attachment' => 'nullable|file|max:10240', // 10MB max
    ];

    public function mount($courseOfferingId)
    {
        $this->courseOfferingId = $courseOfferingId;

        $teacherId = auth()->user()?->teacher?->id;
        $courseOffering = CourseOffering::findOrFail($courseOfferingId);
        if (!$teacherId || $courseOffering->teacher_id !== $teacherId) {
            abort(403, 'لا يمكنك إدارة واجبات هذه المادة.');
        }
        $this->resetForm();
    }

    public function getCourseOfferingProperty()
    {
        return CourseOffering::findOrFail($this->courseOfferingId);
    }

    public function getHomeworksProperty()
    {
        return Homework::where('course_offering_id', $this->courseOfferingId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getAssessmentsProperty()
    {
        return Assessment::where('course_offering_id', $this->courseOfferingId)->get();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->isEditing = false;
    }

    public function edit($id)
    {
        $homework = Homework::findOrFail($id);
        $this->homeworkId = $id;
        $this->title = $homework->title;
        $this->description = $homework->description;
        $this->submission_type = $homework->submission_type->value;
        $this->status = $homework->status->value;
        $this->due_date = $homework->due_date ? $homework->due_date->format('Y-m-d\TH:i') : null;
        $this->allow_late = $homework->allow_late;
        $this->max_score = $homework->max_score;
        $this->assessment_id = $homework->assessment_id;
        $this->existingAttachment = $homework->attachment_path;

        $this->showModal = true;
        $this->isEditing = true;
    }

    public function save(HomeworkService $service)
    {
        $this->validate();

        $data = [
            'course_offering_id' => $this->courseOfferingId,
            'title' => $this->title,
            'description' => $this->description,
            'submission_type' => $this->submission_type,
            'status' => $this->status,
            'due_date' => $this->due_date,
            'allow_late' => $this->allow_late,
            'max_score' => $this->max_score,
            'assessment_id' => $this->assessment_id ?: null,
        ];

        if ($this->attachment) {
            $data['attachment_path'] = $this->attachment->store('homework-attachments', 'public');
        }

        try {
            if ($this->isEditing) {
                $homework = Homework::findOrFail($this->homeworkId);
                $service->updateHomework($homework, $data);
                $this->dispatch('notify', message: 'تم تحديث الواجب بنجاح.');
            } else {
                $service->createHomework($data);
                $this->dispatch('notify', message: 'تم إنشاء الواجب بنجاح.');
            }

            $this->showModal = false;
            $this->resetForm();
        } catch (\Exception $e) {
            $this->addError('due_date', $e->getMessage());
        }
    }

    public function delete($id, HomeworkService $service)
    {
        $homework = Homework::findOrFail($id);
        $service->deleteHomework($homework);
        $this->dispatch('notify', message: 'تم حذف الواجب.');
    }

    private function resetForm()
    {
        $this->reset(['title', 'description', 'submission_type', 'status', 'due_date', 'allow_late', 'max_score', 'assessment_id', 'homeworkId', 'attachment', 'existingAttachment']);
        $this->submission_type = SubmissionType::OFFLINE->value;
        $this->status = HomeworkStatus::DRAFT->value;
        $this->max_score = 10;
    }

    public function render()
    {
        return view('livewire.teacher.homework.homework-manager')
            ->layout('layouts.app');
    }
}

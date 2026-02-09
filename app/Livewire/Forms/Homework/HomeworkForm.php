<?php

namespace App\Livewire\Forms\Homework;

use App\Domains\Academic\Homework\Enums\HomeworkStatus;
use App\Domains\Academic\Homework\Enums\SubmissionType;
use Livewire\Form;
use Livewire\Attributes\Validate;

/**
 * Form Object لإنشاء/تعديل الواجبات المنزلية
 */
class HomeworkForm extends Form
{
    // ============================================
    // بيانات الواجب الأساسية
    // ============================================
    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:2000')]
    public ?string $description = null;

    #[Validate('required')]
    public string $submissionType = '';

    #[Validate('required')]
    public string $status = '';

    #[Validate('nullable|date')]
    public ?string $dueDate = null;

    #[Validate('boolean')]
    public bool $allowLate = false;

    #[Validate('required|numeric|min:0|max:100')]
    public float $maxScore = 10;

    #[Validate('nullable|exists:assessments,id')]
    public ?int $assessmentId = null;

    // ============================================
    // المرفقات
    // ============================================
    public $attachment = null;
    public ?string $existingAttachment = null;

    public function __construct($component, $propertyName)
    {
        parent::__construct($component, $propertyName);
        $this->submissionType = SubmissionType::OFFLINE->value;
        $this->status = HomeworkStatus::DRAFT->value;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'submissionType' => 'required|in:' . implode(',', array_column(SubmissionType::cases(), 'value')),
            'status' => 'required|in:' . implode(',', array_column(HomeworkStatus::cases(), 'value')),
            'dueDate' => 'nullable|date',
            'allowLate' => 'boolean',
            'maxScore' => 'required|numeric|min:0|max:100',
            'assessmentId' => 'nullable|exists:assessments,id',
            'attachment' => 'nullable|file|max:10240', // 10MB
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان الواجب مطلوب',
            'title.max' => 'عنوان الواجب طويل جداً',
            'maxScore.required' => 'الدرجة القصوى مطلوبة',
            'maxScore.min' => 'الدرجة القصوى يجب أن تكون صفر أو أكثر',
        ];
    }

    public function setFromModel(Homework $homework): void
    {
        $this->title = $homework->title;
        $this->description = $homework->description;
        $this->submissionType = $homework->submission_type->value;
        $this->status = $homework->status->value;
        $this->dueDate = $homework->due_date?->format('Y-m-d\TH:i');
        $this->allowLate = $homework->allow_late;
        $this->maxScore = $homework->max_score;
        $this->assessmentId = $homework->assessment_id;
        $this->existingAttachment = $homework->attachment_path;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'submission_type' => $this->submissionType,
            'status' => $this->status,
            'due_date' => $this->dueDate,
            'allow_late' => $this->allowLate,
            'max_score' => $this->maxScore,
            'assessment_id' => $this->assessmentId ?: null,
        ];
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->title = '';
        $this->description = null;
        $this->submissionType = SubmissionType::OFFLINE->value;
        $this->status = HomeworkStatus::DRAFT->value;
        $this->dueDate = null;
        $this->allowLate = false;
        $this->maxScore = 10;
        $this->assessmentId = null;
        $this->attachment = null;
        $this->existingAttachment = null;
    }
}

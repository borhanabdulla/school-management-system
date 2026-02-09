<?php

namespace App\Livewire\Academic;

use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Subject\Services\SubjectService;
use App\Domains\Academic\Subject\Services\SubjectLookupService;
use App\Domains\Academic\Grade\Services\GradeService;
use Illuminate\Validation\ValidationException;
use App\Livewire\Forms\Academic\SubjectForm;
use App\Domains\Academic\Subject\Data\SubjectData;
use App\Domains\Academic\Subject\Exceptions\SubjectHasGradesException;

class SubjectManager extends Component
{
    use WithPagination;

    // --- Library State ---
    public SubjectForm $form;
    public $searchLib = '';
    public $activeTab = 'library';

    // --- Curriculum State ---
    public $selectedGradeId;
    public $alloc_subject_id, $credit_hours = 1, $term_type = 'full_year';
    public $newCurriculumGradeId; // For the create modal selection
    public $editingPivotId = null;

    // Lists (Cached via LookupService)
    public $availableSubjects = [];

    protected $listeners = ['refresh' => '$refresh'];

    public function mount()
    {
        $this->selectedGradeId = null;
    }

    // --- Navigation & Selection ---

    public function startCurriculum()
    {
        $this->validate([
            'newCurriculumGradeId' => 'required|exists:grades,id'
        ]);

        $this->selectedGradeId = $this->newCurriculumGradeId;
        $this->dispatch('close-modal', 'create-curriculum-modal');
    }

    public function openCurriculum($gradeId)
    {
        $this->selectedGradeId = $gradeId;
        $this->activeTab = 'curriculum';
    }

    public function closeCurriculum()
    {
        $this->selectedGradeId = null;
        $this->reset(['alloc_subject_id', 'credit_hours', 'term_type', 'editingPivotId']);
    }

    public function render(SubjectLookupService $lookupService, \App\Domains\Academic\Grade\Services\GradeLookupService $gradeLookupService)
    {
        // 1. تبويب المكتبة (Library) - Cached
        $subjects = $lookupService->getSubjectsList();

        if ($this->searchLib) {
            $subjects = $subjects->filter(function ($subject) {
                return str_contains($subject->name, $this->searchLib) || str_contains($subject->code, $this->searchLib);
            });
        }

        // Pagination for collection
        $perPage = 12;
        $page = request()->input('libPage', 1);
        $paginatedSubjects = new \Illuminate\Pagination\LengthAwarePaginator(
            $subjects->forPage($page, $perPage),
            $subjects->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => 'libPage']
        );

        // 2. تبويب المناهج (Curriculum)
        $gradeSubjects = collect();
        $selectedGrade = null;
        $curriculumStats = [];
        $allGrades = $gradeLookupService->getGradesList();

        if ($this->selectedGradeId) {
            $selectedGrade = $allGrades->firstWhere('id', $this->selectedGradeId);

            if ($selectedGrade) {
                $gradeSubjects = $lookupService->getCurriculumByGrade($this->selectedGradeId);

                $curriculumStats = [
                    'total_subjects' => $gradeSubjects->count(),
                    'total_credits' => $gradeSubjects->sum('pivot.credit_hours'),
                    'theory_count' => $gradeSubjects->where('type', 'theory')->count(),
                    'practical_count' => $gradeSubjects->whereIn('type', ['practical', 'both'])->count(),
                ];
            }
        }

        return view('livewire.academic.subject-manager', [
            'subjects' => $paginatedSubjects,
            'gradeSubjects' => $gradeSubjects,
            'selectedGrade' => $selectedGrade,
            'curriculumStats' => $curriculumStats,
            'allGrades' => $allGrades,
        ]);
    }

    // --- Library Actions ---

    public function createSubject()
    {
        $this->form->reset();
        $this->form->type = 'theory';
        $this->dispatch('open-modal', 'lib-modal');
    }

    public function editSubject(Subject $subject)
    {
        $this->form->setSubject($subject);
        $this->dispatch('open-modal', 'lib-modal');
    }

    public function saveSubject(SubjectService $service)
    {
        if (trim($this->form->code) === '') {
            $this->form->code = null;
        }

        $this->form->validate();

        try {
            if ($this->form->id) {
                $service->updateSubject(Subject::find($this->form->id), $this->form->all());
                $this->dispatch('notify', __('messages.subject_updated'));
            } else {
                $service->createSubject(SubjectData::fromArray($this->form->all()));
                $this->dispatch('notify', __('messages.subject_created'));
            }
            $this->dispatch('close-modal', 'lib-modal');
        } catch (\Exception $e) {
            $this->dispatch('error', $e->getMessage());
        }
    }

    public function deleteSubject($id, SubjectService $service)
    {
        try {
            $service->deleteSubject(Subject::findOrFail($id));
            $this->dispatch('notify', __('messages.subject_deleted'));
        } catch (SubjectHasGradesException $e) {
            $this->dispatch('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->dispatch('error', __('messages.unexpected_error', ['error' => $e->getMessage()]));
        }
    }

    // --- Curriculum Actions ---

    public function openAllocModal(SubjectLookupService $lookupService)
    {
        $this->reset(['alloc_subject_id', 'credit_hours', 'term_type', 'editingPivotId']);
        $this->availableSubjects = $lookupService->getSubjectsList();
        $this->dispatch('open-modal', 'alloc-modal');
    }

    public function editAllocation($pivotId, $subjectId, $credits, $termType, SubjectLookupService $lookupService)
    {
        $this->editingPivotId = $pivotId;
        $this->alloc_subject_id = $subjectId;
        $this->credit_hours = $credits;
        $this->term_type = $termType;

        $this->availableSubjects = $lookupService->getSubjectsList()->where('id', $subjectId);
        $this->dispatch('open-modal', 'alloc-modal');
    }

    public function saveAllocation(SubjectService $service)
    {
        $rules = [
            'credit_hours' => 'required|integer|min:1',
            'term_type' => 'required|in:full_year,term_1,term_2,term_3',
        ];

        if (!$this->editingPivotId) {
            $rules['alloc_subject_id'] = 'required|exists:subjects,id';
        }

        $this->validate($rules);

        $data = [
            'credit_hours' => $this->credit_hours,
            'term_type' => $this->term_type,
        ];

        try {
            if ($this->editingPivotId) {
                $service->updateGradeSubject($this->editingPivotId, $data);
                $this->dispatch('notify', __('messages.curriculum_updated'));
            } else {
                $data['subject_id'] = $this->alloc_subject_id;
                $grade = Grade::find($this->selectedGradeId);

                if (!$grade) {
                    throw ValidationException::withMessages(['selectedGradeId' => __('messages.select_valid_grade')]);
                }

                $service->assignSubjectToGrade($grade, $data);
                $this->dispatch('notify', __('messages.curriculum_created'));
            }
            $this->dispatch('close-modal', 'alloc-modal');
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->toArray();
            if (isset($errors['subject_id'])) {
                $errors['alloc_subject_id'] = $errors['subject_id'];
                unset($errors['subject_id']);
            }
            $this->setErrorBag($errors);
        } catch (\Exception $e) {
            $this->dispatch('error', $e->getMessage());
        }
    }

    public function deleteAllocation($pivotId, SubjectService $service)
    {
        try {
            $service->removeSubjectFromGrade($pivotId);
            $this->dispatch('notify', __('messages.curriculum_deleted'));
        } catch (\Exception $e) {
            $this->dispatch('error', __('messages.general_error'));
        }
    }
}

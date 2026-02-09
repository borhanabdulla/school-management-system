<?php

namespace App\Livewire\Academic;

use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\ClassSection\Services\ClassSectionLookupService;
use App\Domains\Academic\ClassSection\Data\ClassSectionData;
use App\Domains\Academic\ClassSection\Actions\CreateClassSectionAction;
use App\Domains\Academic\ClassSection\Actions\UpdateClassSectionAction;
use App\Domains\Academic\ClassSection\Actions\DeleteClassSectionAction;
use App\Domains\Academic\ClassSection\Actions\CloneClassSectionsAction;
use App\Domains\Academic\AcademicYear\Services\AcademicYearLookupService;
use App\Domains\Academic\Grade\Services\GradeLookupService;
use App\Livewire\Forms\Academic\ClassSectionForm;
use Illuminate\Validation\ValidationException;
use Exception;

class ClassSectionManager extends Component
{
    use WithPagination;

    // Filters
    public $filterYear;
    public $filterGrade;
    public $search = '';

    // Modal States (Handled by Alpine mostly, but kept for form binding)
    public ClassSectionForm $form;

    // Clone Fields
    public $source_year_id = '';
    public $target_year_id = '';

    public function mount(AcademicYearLookupService $lookupService)
    {
        $years = $lookupService->getList();
        $activeYear = $years->where('status', \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Active)->first();
        $this->filterYear = $activeYear ? $activeYear->id : ($years->first()->id ?? null);
    }

    public function render(
        AcademicYearLookupService $yearLookup,
        GradeLookupService $gradeLookup,
        ClassSectionLookupService $sectionLookup
    ) {
        // 1. Get all grades (Cached)
        $grades = $gradeLookup->getGradesList();

        // 2. Filter grades if selected
        if ($this->filterGrade) {
            $grades = $grades->where('id', $this->filterGrade);
        }

        // 3. Load sections for each grade using LookupService (Cached + InMemory Filter)
        $gradesWithSections = $grades->map(function ($grade) use ($sectionLookup) {
            $sections = $sectionLookup->getSections(
                $grade->id,
                (int) $this->filterYear,
                $this->search
            );

            $grade->setRelation('sections', $sections);
            return $grade;
        });

        return view('livewire.academic.class-section-manager', [
            'gradesWithSections' => $gradesWithSections,
            'allYears' => $yearLookup->getList(),
            'allGrades' => $gradeLookup->getGradesList(),
        ]);
    }

    // Reset Pagination triggers
    public function updatedFilterYear()
    {
        $this->resetPage();
    }
    public function updatedFilterGrade()
    {
        $this->resetPage();
    }
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function edit($id)
    {
        $section = ClassSection::findOrFail($id);
        $this->form->setSection($section);
        $this->dispatch('open-modal', isEditing: true);
    }

    public function resetForm()
    {
        $this->form->reset();
        $this->form->academic_year_id = $this->filterYear ?: '';
        $this->form->grade_id = $this->filterGrade ?: '';
        $this->form->is_active = true;
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('open-modal', isEditing: false);
    }

    public function save(
        CreateClassSectionAction $createAction,
        UpdateClassSectionAction $updateAction
    ) {
        $this->form->validate();

        try {
            $data = ClassSectionData::fromArray([
                'name' => $this->form->name,
                'grade_id' => $this->form->grade_id,
                'academic_year_id' => $this->form->academic_year_id,
                'max_capacity' => $this->form->max_capacity,
                'gender_type' => $this->form->gender_type,
                'is_active' => $this->form->is_active,
            ]);

            if ($this->form->id) {
                $section = ClassSection::findOrFail($this->form->id);
                $updateAction->execute($section, $data);
                $this->dispatch('notify', __('تم تحديث الشعبة بنجاح.'));
            } else {
                $createAction->execute($data);
                $this->dispatch('notify', __('تم إنشاء الشعبة بنجاح.'));
            }

            $this->dispatch('close-modal');
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
        } catch (Exception $e) {
            $this->dispatch('error', $e->getMessage());
        }
    }

    public function delete($id, DeleteClassSectionAction $deleteAction)
    {
        try {
            $section = ClassSection::findOrFail($id);
            $deleteAction->execute($section);
            $this->dispatch('notify', __('تم حذف الشعبة.'));
        } catch (Exception $e) {
            $this->dispatch('error', $e->getMessage());
        }
    }

    public function executeClone(CloneClassSectionsAction $cloneAction)
    {
        $this->validate([
            'source_year_id' => 'required|exists:academic_years,id|different:target_year_id',
            'target_year_id' => 'required|exists:academic_years,id',
        ]);

        try {
            $count = $cloneAction->execute(
                (int) $this->source_year_id,
                (int) $this->target_year_id
            );

            $this->dispatch('notify', __("تم نسخ هيكل :count شعبة بنجاح.", ['count' => $count]));
            $this->filterYear = $this->target_year_id;
            $this->dispatch('close-clone-modal');
        } catch (Exception $e) {
            $this->dispatch('error', $e->getMessage());
        }
    }
}
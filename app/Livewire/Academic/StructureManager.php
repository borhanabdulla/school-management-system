<?php

namespace App\Livewire\Academic;

use Livewire\Component;
use App\Domains\Academic\Stage\Models\EducationalStage;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Stage\Services\StageService;
use App\Domains\Academic\Grade\Services\GradeService;
use App\Domains\Academic\Grade\Services\GradeLookupService;
use App\Domains\Academic\Stage\Data\StageData;
use App\Domains\Academic\Grade\Data\GradeData;
use Illuminate\Validation\ValidationException;
use App\Exceptions\Academic\StageHasGradesException;
use App\Domains\Academic\ClassSection\Exceptions\GradeHasSectionsException;
use App\Exceptions\Academic\CircularReferenceException;

class StructureManager extends Component
{
    // State
    public \App\Livewire\Forms\Academic\EducationalStageForm $stageForm;
    public \App\Livewire\Forms\Academic\GradeForm $gradeForm;

    public $showStageModal = false;
    public $showGradeModal = false;
    public $isEditing = false;

    // Lists
    public $allStages = [];
    public $allGrades = [];

    protected $listeners = ['refresh' => '$refresh'];

    public function render(StageService $stageService)
    {
        // استخدام الخدمة لجلب القائمة المكيشة
        $stages = $stageService->getStagesList()->load('grades.nextGrade');
        return view('livewire.academic.structure-manager', ['stages' => $stages]);
    }

    // --- Stage Actions ---

    public function createStage()
    {
        $this->stageForm->reset();
        $this->stageForm->min_passing_percentage = 50;
        $this->isEditing = false;
        $this->showStageModal = true;
    }

    public function editStage(EducationalStage $stage)
    {
        $this->stageForm->setStage($stage);
        $this->isEditing = true;
        $this->showStageModal = true;
    }

    public function saveStage(StageService $service)
    {
        $this->stageForm->validate();

        try {
            $data = StageData::fromArray([
                'name' => $this->stageForm->name,
                'rank' => $this->stageForm->rank,
                'min_passing_percentage' => $this->stageForm->min_passing_percentage,
                'grading_system' => $this->stageForm->grading_system,
            ]);

            if ($this->isEditing) {
                $service->updateStage(EducationalStage::find($this->stageForm->id), $data);
                $this->dispatch('notify', message: 'تم تحديث المرحلة.');
            } else {
                $service->createStage($data);
                $this->dispatch('notify', message: 'تم إنشاء المرحلة.');
            }
            $this->showStageModal = false;
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function deleteStage($id, StageService $service)
    {
        try {
            $service->deleteStage(EducationalStage::findOrFail($id));
            $this->dispatch('notify', message: 'تم حذف المرحلة.');
        } catch (StageHasGradesException $e) {
            $this->dispatch('error', message: 'لا يمكن حذف المرحلة لأنها مرتبطة بصفوف.');
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    // --- Grade Actions ---

    public function updatedGradeFormEducationalStageId($value, GradeLookupService $lookupService = null)
    {
        if ($value) {
            // استخدام GradeLookupService بدلاً من استعلام مباشر (حل الكود الظلي)
            $lookupService = $lookupService ?? app(GradeLookupService::class);
            $this->gradeForm->level_order = $lookupService->getNextLevelOrder((int) $value);
        }
    }

    public function createGrade(StageService $stageService, $stageId = null)
    {
        $this->prepareGradeData($stageService);
        $this->gradeForm->reset();

        if ($stageId) {
            $this->gradeForm->educational_stage_id = $stageId;
            $this->updatedGradeFormEducationalStageId($stageId);
        }
        $this->isEditing = false;
        $this->showGradeModal = true;
    }

    public function editGrade(Grade $grade, StageService $stageService)
    {
        $this->prepareGradeData($stageService);
        $this->gradeForm->setGrade($grade);
        $this->isEditing = true;
        $this->showGradeModal = true;
    }

    public function saveGrade(GradeService $service)
    {
        $this->gradeForm->validate();

        try {
            $data = GradeData::fromArray([
                'educational_stage_id' => $this->gradeForm->educational_stage_id,
                'name' => $this->gradeForm->name,
                'level_order' => $this->gradeForm->level_order,
                'next_grade_id' => $this->gradeForm->next_grade_id ?: null,
            ]);

            if ($this->isEditing) {
                $service->updateGrade(Grade::find($this->gradeForm->id), $data->toArray());
                $this->dispatch('notify', message: 'تم تحديث الصف.');
            } else {
                $service->createGrade($data->toArray());
                $this->dispatch('notify', message: 'تم إنشاء الصف.');
            }
            $this->showGradeModal = false;
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
        } catch (CircularReferenceException $e) {
            $this->addError('gradeForm.next_grade_id', $e->getMessage());
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function deleteGrade($id, GradeService $service)
    {
        try {
            $service->deleteGrade(Grade::findOrFail($id));
            $this->dispatch('notify', message: 'تم حذف الصف.');
        } catch (GradeHasSectionsException $e) {
            $this->dispatch('error', message: 'لا يمكن حذف الصف لوجود شعب مرتبطة.');
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    private function prepareGradeData(StageService $stageService, GradeLookupService $lookupService = null)
    {
        $this->allStages = $stageService->getStagesList();

        // استخدام GradeLookupService بدلاً من استعلام مباشر (حل الكود الظلي)
        $lookupService = $lookupService ?? app(GradeLookupService::class);
        $this->allGrades = $lookupService->getGradesForNextSelection($this->gradeForm->id);
    }
}

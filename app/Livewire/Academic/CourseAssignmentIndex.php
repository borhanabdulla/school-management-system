<?php

namespace App\Livewire\Academic;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Stage\Models\EducationalStage;
use App\Domains\Academic\Grade\Models\Grade;
use App\Infrastructure\Context\AcademicContextService;

class CourseAssignmentIndex extends Component
{
    public int $academicYearId;
    public string $academicYearName;

    // Filters
    public string $search = '';
    public string $stageId = '';
    public string $gradeId = '';

    public function mount(AcademicContextService $context)
    {
        // Get active academic year
        $activeYear = $context->activeYear();

        if (!$activeYear) {
            abort(403, 'عذراً، لا توجد سنة دراسية نشطة حالياً.');
        }

        $this->academicYearId = $activeYear->id;
        $this->academicYearName = $activeYear->name;
    }

    #[Computed]
    public function stages()
    {
        return EducationalStage::orderBy('rank')->get();
    }

    #[Computed]
    public function grades()
    {
        $query = Grade::with('stage')->orderBy('educational_stage_id')->orderBy('level_order');

        if ($this->stageId) {
            $query->where('educational_stage_id', $this->stageId);
        }

        return $query->get();
    }

    #[Computed]
    public function sections()
    {
        $query = ClassSection::where('academic_year_id', $this->academicYearId)
            ->with(['grade.stage']);

        // Apply filters
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhereHas('grade', function ($gradeQuery) {
                        $gradeQuery->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->gradeId) {
            $query->where('grade_id', $this->gradeId);
        } elseif ($this->stageId) {
            $query->whereHas('grade', function ($q) {
                $q->where('educational_stage_id', $this->stageId);
            });
        }

        return $query->orderBy('grade_id')
            ->orderBy('name')
            ->get()
            ->groupBy(fn($section) => $section->grade->stage->name);
    }

    #[Computed]
    public function stats()
    {
        $allSections = ClassSection::where('academic_year_id', $this->academicYearId)->get();

        return [
            'total_sections' => $allSections->count(),
            'filtered_sections' => $this->sections->flatten()->count(),
        ];
    }

    public function updatedStageId()
    {
        $this->gradeId = ''; // Reset grade when stage changes
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->stageId = '';
        $this->gradeId = '';
    }

    public function render()
    {
        return view('livewire.academic.course-assignment-index')
            ->layout('layouts.app');
    }
}

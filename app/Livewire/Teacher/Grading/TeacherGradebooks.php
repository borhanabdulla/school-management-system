<?php

namespace App\Livewire\Teacher\Grading;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Domains\Academic\Grading\Models\GradebookSettings;

#[Layout('layouts.app')]
class TeacherGradebooks extends Component
{
    public ?int $academicYearId = null;

    public function mount()
    {
        abort_unless(auth()->user()->can('grading.view_gradebook'), 403, 'ليس لديك صلاحية لعرض دفتر الدرجات.');
        $this->academicYearId = school()->activeYearId();
    }

    #[Computed]
    public function teacher(): ?Teacher
    {
        return auth()->user()->teacher;
    }

    #[Computed]
    public function gradebooks()
    {
        if (!$this->teacher) {
            return collect();
        }

        $activeTermId = app(\App\Infrastructure\Context\AcademicContextService::class)->activeTerm()?->id;

        return CourseOffering::where('teacher_id', $this->teacher->id)
            ->when($this->academicYearId, fn($q) => $q->where('academic_year_id', $this->academicYearId))
            ->where(function ($q) use ($activeTermId) {
                $q->where('term_id', $activeTermId)
                    ->orWhereNull('term_id');
            })
            ->with([
                'subject',
                'classSection.grade',
                'classSection' => fn($q) => $q->withCount('students'),
                'term',
            ])
            ->get()
            ->groupBy(fn($co) => $co->classSection->grade->name ?? 'غير محدد');
    }

    public function getProgress(CourseOffering $courseOffering): int
    {
        $studentCount = $courseOffering->classSection->students_count ?? 0;
        if ($studentCount === 0)
            return 0;

        $termId = $courseOffering->term_id;
        if (!$termId) {
            return 0;
        }

        $monthsCount = \App\Domains\Academic\Grading\Models\GradebookMonth::where('term_id', $termId)->count();
        if ($monthsCount === 0) {
            return 0;
        }

        $settings = GradebookSettings::findForYear($courseOffering->academic_year_id);
        $categories = GradebookSettings::normalizeMonthlyCategories(
            $settings?->monthly_categories ?? GradebookSettings::getDefaultCategories()
        );
        $categoriesCount = count($categories);
        if ($categoriesCount === 0) {
            return 0;
        }

        $totalPossible = $studentCount * $monthsCount * $categoriesCount;

        $recorded = \App\Domains\Academic\Grading\Models\MonthlyGrade::where('course_offering_id', $courseOffering->id)
            ->whereNotNull('score')
            ->selectRaw('student_id, gradebook_month_id, COALESCE(category_key, category) as category_key')
            ->distinct()
            ->count();

        if ($totalPossible === 0)
            return 0;

        return min(100, round(($recorded / $totalPossible) * 100));
    }

    #[Computed]
    public function academicYears()
    {
        return AcademicYear::orderByDesc('start_date')->get();
    }

    public function render()
    {
        return view('livewire.teacher.grading.teacher-gradebooks');
    }
}

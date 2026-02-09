<?php

namespace App\Livewire\Teacher\Homework;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

#[Layout('layouts.app')]
class TeacherHomeworkDashboard extends Component
{
    public ?int $academicYearId = null;
    public $selectedGrade = null;

    public function mount()
    {
        $activeYear = school()->activeYear();
        $this->academicYearId = $activeYear?->id;
    }

    #[Computed]
    public function teacher()
    {
        // Get teacher from staff relation
        $user = auth()->user();

        // Try to find teacher through staff
        $staff = \App\Domains\HR\Staff\Models\Staff::where('user_id', $user->id)->first();
        if ($staff) {
            return \App\Domains\HR\Teacher\Models\Teacher::where('staff_id', $staff->id)->first();
        }

        return null;
    }

    #[Computed]
    public function courseOfferings()
    {
        if (!$this->teacher) {
            return collect();
        }

        return CourseOffering::where('teacher_id', $this->teacher->id)
            ->when($this->academicYearId, fn($q) => $q->where('academic_year_id', $this->academicYearId))
            ->with([
                'subject',
                'classSection.grade',
                'classSection' => fn($q) => $q->withCount('students'),
            ])
            ->withCount('homeworks')
            ->get()
            ->groupBy(fn($co) => $co->classSection->grade->name ?? 'غير محدد');
    }

    #[Computed]
    public function academicYears()
    {
        return AcademicYear::orderByDesc('start_date')->get();
    }

    public function getHomeworkStats(CourseOffering $offering): array
    {
        $homeworks = $offering->homeworks;
        $published = $homeworks->where('status', 'published')->count();
        $draft = $homeworks->where('status', 'draft')->count();

        return [
            'total' => $homeworks->count(),
            'published' => $published,
            'draft' => $draft,
        ];
    }

    public function render()
    {
        return view('livewire.teacher.homework.teacher-homework-dashboard');
    }
}

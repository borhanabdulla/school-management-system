<?php

namespace App\Livewire\Academic;

use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Services\CourseOfferingService;
use App\Domains\HR\Teacher\Services\TeacherService;
use App\Infrastructure\Context\AcademicContextService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ClassSectionCoursesManager extends Component
{
    public $sectionId;
    public $section;
    public $assignments = []; // [subject_id => teacher_id]
    public $teachers = [];
    public $activeYear;
    public $subjects = [];

    public function mount($sectionId, AcademicContextService $contextService, CourseOfferingService $assignmentService, TeacherService $teacherService)
    {
        $this->sectionId = $sectionId;
        $this->activeYear = $contextService->activeYear();

        if (!$this->activeYear) {
            abort(404, 'No active academic year found.');
        }

        $this->section = ClassSection::with('grade')->findOrFail($sectionId);

        // Load Teachers (Cached)
        $this->teachers = $teacherService->getTeachersList();

        // Load Matrix
        $matrix = $assignmentService->getAssignmentMatrix($sectionId, $this->activeYear->id);

        foreach ($matrix as $item) {
            $this->subjects[] = [
                'id' => $item['subject_id'],
                'name' => $item['subject_name'],
                'code' => $item['subject_code'],
            ];

            // Initialize assignment
            $this->assignments[$item['subject_id']] = $item['assigned_teacher_id'];
        }
    }

    public function save(CourseOfferingService $assignmentService)
    {
        foreach ($this->assignments as $subjectId => $teacherId) {
            if (empty($teacherId)) {
                $assignmentService->removeTeacherFromSubject(
                    $this->sectionId,
                    $subjectId,
                    $this->activeYear->id
                );
                continue;
            }

            $assignmentService->assignTeacherToSubject(
                $this->sectionId,
                $subjectId,
                $teacherId,
                $this->activeYear->id
            );
        }

        $this->dispatch('notify', message: 'تم حفظ التعيينات بنجاح.');
    }

    public function render()
    {
        return view('livewire.academic.class-section-courses-manager');
    }
}

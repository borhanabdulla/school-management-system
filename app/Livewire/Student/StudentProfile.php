<?php

namespace App\Livewire\Student;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;
use App\Domains\Academic\Student\Services\StudentLookupService;
use App\Livewire\Forms\Student\StudentUpdateForm;
use App\Domains\Academic\Student\Actions\UpdateStudentAction;
use App\Domains\Academic\Student\Actions\DeleteStudentAction;
use App\Domains\Academic\Student\Exceptions\StudentDeleteBlockedException;
use App\Domains\Academic\Student\Data\StudentUpdateData;
use App\Domains\Academic\Student\Services\StudentPerformanceService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

#[Layout('layouts.app')]
class StudentProfile extends Component
{
    #[Locked]
    public $studentId;

    #[Url(as: 'tab')]
    public $activeTab = 'profile';
    public $showEditModal = false;
    public StudentUpdateForm $form;

    protected ?StudentLookupService $studentLookup = null;

    /**
     * Computed Property للطالب - يتم تحميله مرة واحدة فقط
     */
    #[Computed]
    protected function lookup(): StudentLookupService
    {
        return $this->studentLookup ??= app(StudentLookupService::class);
    }

    #[Computed]
    public function student()
    {
        $student = $this->lookup()->findForShow($this->studentId);
        if (!$student) {
            abort(404);
        }

        return $student;
    }

    public function mount(int $id)
    {
        abort_unless(auth()->user()->can('students.view'), 403, 'ليس لديك صلاحية عرض بيانات الطلاب.');
        $this->studentId = $id;
    }

    public function delete(DeleteStudentAction $action)
    {
        try {
            $student = $this->student;
            $action->execute($student);

            $this->dispatch('notify', message: 'تم حذف الطالب بنجاح.');
            return redirect()->route('students.index');

        } catch (StudentDeleteBlockedException $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete student', [
                'student_id' => $this->studentId,
                'error' => $e->getMessage()
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()
            ]);
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function edit()
    {
        $this->form->setStudent($this->student);
        $this->showEditModal = true;
    }

    public function update(UpdateStudentAction $action)
    {
        $this->form->validate();

        try {
            $data = StudentUpdateData::fromForm($this->form);
            $action->execute($this->student, $data);

            // إعادة تحميل البيانات
            unset($this->student);

            $this->showEditModal = false;

            // Logging
            Log::info('Student updated successfully', [
                'student_id' => $this->studentId,
                'updated_by' => auth()->id()
            ]);

            // Notification
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'تم تحديث بيانات الطالب بنجاح.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;

        } catch (\Exception $e) {
            Log::error('Failed to update student', [
                'student_id' => $this->studentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'حدث خطأ أثناء التحديث. يرجى المحاولة مرة أخرى.'
            ]);
        }
    }

    public bool $canDelete = false;
    public array $deleteBlockers = [];

    public function render(StudentPerformanceService $performanceService, DeleteStudentAction $deleteAction)
    {
        $student = $this->student;

        // Check delete status
        $this->deleteBlockers = $deleteAction->checkBlockers($student);
        $this->canDelete = empty($this->deleteBlockers);

        // Cache للبيانات التحليلية
        // Note: These keys must match UpdateStudentAction::clearStudentCache
        $performanceSummary = Cache::remember(
            "student.{$this->studentId}.performance.summary",
            now()->addHours(1),
            fn() => $performanceService->getPerformanceSummary($student)
        );

        $detailedCourses = Cache::remember(
            "student.{$this->studentId}.performance.courses",
            now()->addHours(1),
            fn() => $performanceService->getDetailedCourses($student)
        );

        $recommendations = Cache::remember(
            "student.{$this->studentId}.performance.recommendations",
            now()->addHours(1),
            fn() => $performanceService->getRecommendations($student)
        );

        return view('livewire.student.student-profile', [
            'student' => $student,
            'performanceSummary' => $performanceSummary,
            'detailedCourses' => $detailedCourses,
            'recommendations' => $recommendations,
            'canDelete' => $this->canDelete,
            'deleteBlockers' => $this->deleteBlockers,
        ]);
    }
}

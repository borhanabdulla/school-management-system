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

    #[Computed]
    public function stats(): array
    {
        $student = $this->student;

        return [
            [
                'title' => 'المستوى الحالي',
                'value' => $student->currentClassSection?->name ?? 'غير محدد',
                'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                'variant' => 'info',
            ],
            [
                'title' => 'أولياء الأمور',
                'value' => $student->guardians->count(),
                'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                'variant' => 'primary',
            ],
            [
                'title' => 'سنوات الدراسة',
                'value' => $student->enrollments->count() . ' سنوات',
                'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                'variant' => 'success',
            ],
            [
                'title' => 'حالة الحساب',
                'value' => $student->user ? 'مفعل' : 'غير مفعل',
                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'variant' => $student->user ? 'success' : 'neutral',
            ],
        ];
    }

    public function mount(int $id)
    {
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

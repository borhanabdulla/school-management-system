<?php

namespace App\Livewire\Student;

use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\Academic\Grade\Services\GradeLookupService;
use App\Domains\Academic\ClassSection\Services\ClassSectionLookupService;
use Livewire\Attributes\Computed;
use App\Domains\Academic\Student\Services\StudentLookupService;
use App\Domains\Academic\Student\Data\StudentDirectoryFilterData;
use App\Domains\Academic\Student\Actions\DeleteStudentAction;
use App\Domains\Academic\Student\Exceptions\StudentDeleteBlockedException;

class StudentDirectory extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedAcademicYear = '';
    public $selectedGrade = '';
    public $selectedSection = '';
    public $selectedStatus = '';
    public $selectedFinancialStatus = '';

    protected ?StudentLookupService $studentLookup = null;

    // Bulk Actions
    public $selectedStudents = [];
    public $selectAll = false;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedAcademicYear()
    {
        // Reset dependent filters when academic year changes
        $this->selectedGrade = '';
        $this->selectedSection = '';
        $this->resetPage();
    }

    public function updatedSelectedGrade()
    {
        // Reset section when grade changes
        $this->selectedSection = '';
        $this->resetPage();
    }

    public function updatedSelectedSection()
    {
        $this->resetPage();
    }

    public function updatedSelectedStatus()
    {
        $this->resetPage();
    }

    public function updatedSelectedFinancialStatus()
    {
        $this->resetPage();
    }

    protected function lookup(): StudentLookupService
    {
        return $this->studentLookup ??= app(StudentLookupService::class);
    }

    protected function filtersDto(): StudentDirectoryFilterData
    {
        return new StudentDirectoryFilterData(
            search: $this->search ?: null,
            academicYearId: $this->selectedAcademicYear ?: null,
            gradeId: $this->selectedGrade ?: null,
            sectionId: $this->selectedSection ?: null,
            status: $this->selectedStatus ?: null,
            financialStatus: $this->selectedFinancialStatus ?: null,
            perPage: 15,
            sortBy: 'students.created_at',
            sortDirection: 'desc'
        );
    }

    #[Computed]
    public function academicYears()
    {
        return app(\App\Domains\Academic\AcademicYear\Services\AcademicYearLookupService::class)->getList();
    }

    #[Computed]
    public function grades()
    {
        // الصفوف الدراسية ثابتة ولا تتغير بتغير السنة
        // لكن نحتاج التصفية حسب الشُعب الموجودة في السنة المختارة
        return app(GradeLookupService::class)->getGradesList();
    }

    #[Computed]
    public function sections()
    {
        if (!$this->selectedGrade || !$this->selectedAcademicYear) {
            return [];
        }

        return app(ClassSectionLookupService::class)
            ->getSections($this->selectedGrade, $this->selectedAcademicYear);
    }

    #[Computed]
    public function stats()
    {
        return $this->lookup()->getDirectoryStats($this->filtersDto());
    }

    public function render()
    {
        $students = $this->lookup()->paginateForDirectory($this->filtersDto());
        $financialStatuses = $this->lookup()->mapFinancialStatuses($students->getCollection());

        return view('livewire.student.student-directory', [
            'students' => $students,
            'financialStatuses' => $financialStatuses,
        ]);
    }

    // Bulk Actions
    public function exportSelected()
    {
        $students = $this->lookup()->findManyByIds($this->selectedStudents);

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=students_export_" . date('Y-m-d_H-i') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($students) {
            $file = fopen('php://output', 'w');

            // Add BOM for Excel UTF-8 compatibility
            fputs($file, "\xEF\xBB\xBF");

            // Header Row
            fputcsv($file, [
                'الاسم بالكامل',
                'رقم القبول',
                'الرقم القومي',
                'الصف الدراسي',
                'الشعبة',
                'ولي الأمر',
                'رقم هاتف ولي الأمر',
                'الحالة'
            ]);

            foreach ($students as $student) {
                $guardian = $student->guardians->first();

                fputcsv($file, [
                    $student->full_name_ar,
                    $student->admission_number,
                    $student->national_id,
                    $student->currentGrade?->name ?? '-',
                    $student->currentClassSection?->name ?? '-',
                    $guardian ? $guardian->full_name : '-',
                    $guardian ? $guardian->phone : '-',
                    $student->status->label()
                ]);
            }

            fclose($file);
        };

        // Clear selection after export
        $this->selectedStudents = [];

        return response()->stream($callback, 200, $headers);
    }

    public function sendSmsToGuardians()
    {
        $count = count($this->selectedStudents);

        if ($count === 0) {
            return;
        }

        // Simulation of SMS sending logic
        // In a real app, we would queue a job here:
        // SendSmsToGuardiansJob::dispatch($this->selectedStudents, "رسالة تجريبية من المدرسة");

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "تم جدولة إرسال الرسائل النصية إلى أولياء أمور {$count} طالب بنجاح"
        ]);

        // Clear selection
        $this->selectedStudents = [];
    }
    public function delete($id, DeleteStudentAction $action)
    {
        try {
            $student = $this->lookup()->findForDelete($id);

            if (!$student) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'الطالب غير موجود.'
                ]);
                return;
            }

            $action->execute($student);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'تم حذف الطالب بنجاح.'
            ]);

        } catch (StudentDeleteBlockedException $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to delete student', [
                'student_id' => $id,
                'error' => $e->getMessage()
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()
            ]);
        }
    }

    public function deleteSelected(DeleteStudentAction $action)
    {
        $deletedCount = 0;
        $blockedCount = 0;
        $errors = [];

        foreach ($this->selectedStudents as $id) {
            try {
                $student = $this->lookup()->findForDelete($id);

                if (!$student)
                    continue;

                $action->execute($student);
                $deletedCount++;

            } catch (StudentDeleteBlockedException $e) {
                $blockedCount++;
            } catch (\Exception $e) {
                $errors[] = "طالب #{$id}: {$e->getMessage()}";
            }
        }

        $this->selectedStudents = [];

        if ($deletedCount > 0) {
            $message = "تم حذف {$deletedCount} طالب بنجاح.";
            if ($blockedCount > 0) {
                $message .= " (تم تخطي {$blockedCount} طالب لوجود بيانات مرتبطة)";
            }
            $this->dispatch('notify', ['type' => 'success', 'message' => $message]);
        } elseif ($blockedCount > 0) {
            $this->dispatch('notify', ['type' => 'error', 'message' => "لم يتم حذف أي طالب. جميع الطلاب المحددين ({$blockedCount}) لديهم بيانات مرتبطة."]);
        }

        if (!empty($errors)) {
            \Illuminate\Support\Facades\Log::error('Bulk delete errors', ['errors' => $errors]);
        }
    }
}

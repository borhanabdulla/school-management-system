<?php

namespace App\Livewire\Teacher;


use App\Domains\Academic\Grade\Services\GradeLookupService;
use App\Domains\Academic\Subject\Services\SubjectLookupService;
use App\Domains\Academic\ClassSection\Services\ClassSectionLookupService;
use App\Domains\HR\Teacher\Services\TeacherService;
use App\Livewire\Forms\Teacher\TeacherFilterForm;
use Livewire\Component;
use Livewire\WithPagination;

class TeacherIndex extends Component
{
    use WithPagination;

    public TeacherFilterForm $filters;

    public function render(
        TeacherService $teacherService,
        GradeLookupService $gradeLookup,
        SubjectLookupService $subjectLookup,
        ClassSectionLookupService $sectionLookup,
        \App\Domains\Academic\AcademicYear\Services\AcademicYearLookupService $academicYearLookup
    ) {
        return view('livewire.teacher.teacher-index', [
            // البيانات الديناميكية (الجدول)
            'teachers' => $teacherService->getTeachersList($this->filters->toFiltersArray()),

            // بيانات القوائم (Lookups) - تمرر للواجهة
            'grades' => $gradeLookup->getGradesList(),
            'subjects' => $subjectLookup->getSubjectsList(),
            'academicYears' => $academicYearLookup->getList(),

            // 🔥 المصفوفة المسطحة لـ Alpine.js
            'allSections' => $sectionLookup->getAllActiveSectionsFlat(),
        ]);
    }

    // إعادة تعيين الصفحة عند البحث
    public function updatedFilters()
    {
        $this->resetPage();
    }

    public function delete($id, \App\Domains\HR\Teacher\Actions\DeleteTeacherAction $deleteAction)
    {
        try {
            $teacher = \App\Domains\HR\Teacher\Models\Teacher::findOrFail($id);

            // ✅ PR-6: استخدام Action الموحد للحذف مع كافة التحقيقات الوقائية
            $deleteAction->execute($teacher);

            $this->dispatch('notify', message: 'تم حذف دور المعلم بنجاح (مع الاحتفاظ بالسجل الوظيفي).');
        } catch (\DomainException $e) {
            // أخطاء المنطق (مثل وجود حصص أو بدلاء)
            $this->dispatch('error', message: $e->getMessage());
        } catch (\Exception $e) {
            // أخطاء غير متوقعة
            $this->dispatch('error', message: 'حدث خطأ أثناء الحذف: ' . $e->getMessage());
        }
    }
}

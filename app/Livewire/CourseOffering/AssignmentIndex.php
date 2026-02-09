<?php

namespace App\Livewire\CourseOffering;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed; // استدعاء السمة الجديدة
use App\Infrastructure\Context\AcademicContextService;
use App\Domains\Academic\Term\Services\TermLookupService;
use App\Domains\Academic\Grade\Services\GradeLookupService;
use App\Domains\Academic\ClassSection\Services\ClassSectionLookupService;
use App\Domains\HR\Teacher\Services\TeacherLookupService;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Livewire\CourseOffering\Forms\AssignTeacherForm;
use App\Domains\Academic\Subject\Models\Subject;

class AssignmentIndex extends Component
{
    use WithPagination;

    // الخصائص العامة (فقط للـ State وحقول الإدخال)
    public $filters = ['term_id' => '', 'grade_id' => '', 'section_id' => ''];
    public AssignTeacherForm $form;

    // ❌ تم حذف public $lookup (هذا كان سبب المشكلة الرئيسية)
    // ❌ تم حذف public $coverage (سنحسبها تلقائياً)

    #[Locked]
    public $showAllTeachers = [];

    public function mount()
    {
        // تعيين القيمة الافتراضية للفلاتر فقط
        // نستخدم app() للوصول للسيرفس مرة واحدة هنا
        $this->filters['term_id'] = app(AcademicContextService::class)->activeTermId() ?? '';
        if ($this->filters['term_id'] === '') {
            $this->dispatch('error', message: 'يجب تفعيل ترم قبل إدارة توزيع المواد.');
        }
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    public function toggleShowAll($offeringId)
    {
        $this->showAllTeachers[$offeringId] = !($this->showAllTeachers[$offeringId] ?? false);
    }

    // --- Computed Properties (البيانات المحسوبة) ---

    #[Computed]
    public function offerings()
    {
        // 1. استعلام البيانات (كان سابقاً في render)
        return CourseOffering::with([
            'subject',
            'teacher.staff',
            'classSection.grade.educationalStage',
            'term'
        ])
            ->when($this->filters['term_id'], fn($q) => $q->where('term_id', $this->filters['term_id']))
            ->when($this->filters['grade_id'], fn($q) => $q->whereHas('classSection', fn($qq) => $qq->where('grade_id', $this->filters['grade_id'])))
            ->when($this->filters['section_id'], fn($q) => $q->where('class_section_id', $this->filters['section_id']))
            ->latest()
            ->paginate(20);
    }

    #[Computed]
    public function coverage()
    {
        // 2. حساب نسبة التغطية (يتم تحديثه تلقائياً عند تغيير الفلاتر)
        $termId = $this->filters['term_id'];

        if (!$termId)
            return 0;

        $total = CourseOffering::where('term_id', $termId)->count();
        $assigned = CourseOffering::where('term_id', $termId)->whereNotNull('teacher_id')->count();

        return $total > 0 ? round(($assigned / $total) * 100) : 0;
    }

    #[Computed]
    public function terms()
    {
        return app(TermLookupService::class)->getActiveTerms();
    }

    #[Computed]
    public function grades()
    {
        return app(GradeLookupService::class)->getGradesList();
    }

    #[Computed]
    public function sections()
    {
        $sectionLookup = app(ClassSectionLookupService::class);
        $context = app(AcademicContextService::class);

        // إذا تم اختيار صف معين، نعرض شعبه فقط
        if ($this->filters['grade_id']) {
            $termId = $this->filters['term_id'] ?: $context->activeTermId();
            // Note: getSectionsByGrade takes academicYearId, not termId.
            // But here logic uses termId? Original code used getCurrentTerm()?->id.
            // Wait, getSectionsByGrade signature is (int $gradeId, ?int $academicYearId = null).
            // So passing termId is WRONG if it expects yearId.
            // Let's check original code: $lookup->getSectionsByGrade($this->filters['grade_id'], $termId);
            // If original code passed termId, it might be a bug or I misunderstood getSectionsByGrade.
            // getSectionsByGrade in LookupService: public function getSectionsByGrade($gradeId, $yearId = null)
            // So it expects YearId.
            // But here it passes termId?
            // "termId = ... getCurrentTerm()?->id"
            // This looks like a bug in original code or variable naming confusion.
            // However, I should use activeYearId here.
            $yearId = $context->activeYearId();
            return $sectionLookup->getSectionsByGrade((int)$this->filters['grade_id'], $yearId);
        }

        // وإلا نعرض جميع الشعب
        return $sectionLookup->getAllActiveSectionsFlat();
    }

    // --- Helper Methods ---

    /**
     * هذه الدالة تستقبل parameters من الـ Blade لذلك لا يمكن جعلها Computed
     * لكن قمنا بإصلاحها لتستخدم app() بدلاً من $this->lookup
     */
    public function getFilteredTeachers($subjectId, $termId, $offeringId)
    {
        // ✅ الإصلاح: استدعاء السيرفس محلياً بدلاً من المتغير العام
        $lookup = app(TeacherLookupService::class);
        $allTeachers = $lookup->getAvailableTeachersForSubject($subjectId, $termId);

        if ($this->showAllTeachers[$offeringId] ?? false) {
            return $this->sortTeachersByPriority($allTeachers, $subjectId);
        }

        $subject = Subject::find($subjectId);
        $subjectName = $subject->name ?? '';

        $filtered = collect($allTeachers)->filter(function ($teacher) use ($subjectName) {
            $spec = $teacher['specialization'] ?? '';
            return stripos($spec, $subjectName) !== false ||
                stripos($subjectName, $spec) !== false;
        });

        $teachers = $filtered->isEmpty() ? $allTeachers : $filtered->values()->all();

        return $this->sortTeachersByPriority($teachers, $subjectId);
    }

    private function sortTeachersByPriority($teachers, $subjectId)
    {
        $subject = Subject::find($subjectId);
        $subjectName = $subject->name ?? '';

        return collect($teachers)
            ->map(function ($teacher) use ($subjectName) {
                $spec = $teacher['specialization'] ?? '';
                $hasSpecialization = stripos($spec, $subjectName) !== false ||
                    stripos($subjectName, $spec) !== false;

                if ($hasSpecialization && !$teacher['is_overloaded']) {
                    $priority = 1;
                } elseif ($hasSpecialization && $teacher['is_overloaded']) {
                    $priority = 2;
                } else {
                    $priority = 3;
                }

                return array_merge($teacher, ['priority' => $priority]);
            })
            ->sortBy([
                ['priority', 'asc'],
                ['load_percentage', 'asc'],
            ])
            ->values()
            ->all();
    }

    public function assignTeacher(CourseOffering $offering)
    {
        // تم تصحيح خطأ بسيط هنا (إسناد القيمة لنفسها لم يكن له داع)
        $this->form->setCourseOffering($offering);
        $this->form->save();

        // إعادة حساب التغطية ستتم تلقائياً لأن coverage خاصية محسوبة
    }

    protected $listeners = ['teacher-assigned' => '$refresh'];

    public function render()
    {
        return view('livewire.course-offering.assignment-index')
            ->layout('layouts.app');
    }
}

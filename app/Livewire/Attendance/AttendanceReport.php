<?php

namespace App\Livewire\Attendance;

use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Attendance\Services\AttendanceReportService;
use App\Domains\HR\Teacher\Services\TeacherLookupService;
use App\Infrastructure\Context\AcademicContextService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Carbon\Carbon;

#[Layout('layouts.app')]
class AttendanceReport extends Component
{
    // الفلاتر
    public int $month = 0;
    public int $year = 0;
    public ?int $classSectionId = null;
    public ?int $termId = null;


    // بيانات مساعدة
    public $activeYear = null;
    public ?int $teacherId = null;


    public function mount(TeacherLookupService $teacherLookup, AcademicContextService $academicContext)
    {
        // السنة الدراسية والمعلم
        $this->activeYear = $academicContext->activeYear();
        $this->termId = $academicContext->activeTerm()?->id;

        // التاريخ الافتراضي: الشهر والسنة من السنة النشطة (fallback: الآن)
        if ($this->activeYear) {
            $this->month = (int) $this->activeYear->start_date->format('m');
            $this->year = (int) $this->activeYear->start_date->format('Y');
        } else {
            $this->month = now()->month;
            $this->year = now()->year;
        }

        // الحصول على معرف المعلم من المستخدم الحالي
        if (auth()->check() && auth()->user()->teacher) {
            $this->teacherId = auth()->user()->teacher->id;
        }

        // اختيار أول شعبة افتراضياً
        $sections = $this->availableSections;
        if ($sections->isNotEmpty()) {
            $this->classSectionId = $sections->first()->id;
        }
    }

    /**
     * الشعب المتاحة للمعلم
     */
    #[Computed]
    public function availableSections()
    {

        if (!$this->teacherId)
            return collect();

        return CourseOffering::query()
            ->where('teacher_id', $this->teacherId)
            ->where('academic_year_id', $this->activeYear?->id)
            ->with('classSection:id,name,grade_id', 'classSection.grade:id,name')
            ->get()
            ->pluck('classSection')
            ->unique('id')
            ->filter();
    }

    /**
     * بيانات التقرير
     */
    #[Computed]
    public function reportData()
    {
        if (!$this->classSectionId) {
            return [
                'students' => collect(),
                'days' => [],
                'matrix' => [],
                'stats' => [],
            ];
        }

        $service = app(AttendanceReportService::class);
        return $service->getMonthlyReport($this->classSectionId, $this->month, $this->year, $this->termId);
    }

    /**
     * عدد أيام الدراسة الفعلية
     */
    #[Computed]
    public function schoolDaysCount()
    {
        $service = app(AttendanceReportService::class);
        return $service->getSchoolDaysCount($this->month, $this->year);
    }

    /**
     * قائمة الشهور للاختيار
     */
    #[Computed]
    public function months()
    {
        return collect(range(1, 12))->mapWithKeys(fn($m) => [
            $m => Carbon::create(null, $m, 1)->locale('ar')->monthName
        ]);
    }

    /**
     * قائمة السنوات للاختيار
     */
    #[Computed]
    public function years()
    {
        if ($this->activeYear) {
            $startYear = (int) $this->activeYear->start_date->format('Y');
            $endYear = (int) $this->activeYear->end_date->format('Y');
            return collect(range($startYear, $endYear));
        }

        $currentYear = now()->year;
        return collect(range($currentYear - 2, $currentYear + 1));
    }

    /**
     * تحديث الفلتر
     */
    public function updateFilter()
    {
        // Livewire سيحدث تلقائياً عند تغيير الخصائص
    }

    public function render()
    {
        return view('livewire.attendance.attendance-report');
    }
}

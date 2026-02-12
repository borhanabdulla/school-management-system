<?php

namespace App\Livewire\Attendance;

use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\GradebookMonth;
use App\Domains\Academic\Attendance\Services\AttendanceReportService;
use App\Domains\Academic\Attendance\Services\AttendanceSettingsService;
use App\Domains\Academic\Attendance\Enums\AttendanceResponsibility;
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
    public $activeTerm = null;
    public ?int $teacherId = null;
    public string $responsibleRole = AttendanceResponsibility::SubjectTeacher->value;


    public function mount(
        TeacherLookupService $teacherLookup,
        AcademicContextService $academicContext,
        AttendanceSettingsService $attendanceSettings
    ) {
        // السنة الدراسية والترم النشط
        $this->activeYear = $academicContext->activeYear();
        $this->activeTerm = $academicContext->activeTerm();
        $this->termId = $this->activeTerm?->id;

        // إعدادات الحضور (تحدد مسؤولية الرصد)
        if ($this->activeYear) {
            $settings = $attendanceSettings->getSettings($this->activeYear->id);
            $this->responsibleRole = $settings->responsible_role->value ?? AttendanceResponsibility::SubjectTeacher->value;
        }

        // التاريخ الافتراضي: الشهر والسنة من الترم النشط (fallback: السنة النشطة ثم الآن)
        $defaultDate = $this->activeTerm?->start_date
            ?? $this->activeYear?->start_date
            ?? now();
        $this->month = (int) $defaultDate->format('m');
        $this->year = (int) $defaultDate->format('Y');

        // الحصول على معرف المعلم من المستخدم الحالي
        if (auth()->check()) {
            $this->teacherId = $teacherLookup->getTeacherIdForUser(auth()->id())
                ?? auth()->user()->teacher?->id;
        }

        // اختيار أول شعبة افتراضياً
        $sections = $this->availableSections;
        if ($sections->isNotEmpty()) {
            $this->classSectionId = $sections->first()->id;
        } else {
            $this->classSectionId = null;
        }

        // تأكد أن الشهر الافتراضي ضمن أشهر الترم النشط
        $months = $this->months;
        if ($months->isNotEmpty() && ! $months->has($this->month)) {
            $this->month = (int) $months->keys()->first();
        }
    }

    /**
     * الشعب المتاحة للمعلم
     */
    #[Computed]
    public function availableSections()
    {
        if (!$this->teacherId || !$this->activeYear) {
            return collect();
        }

        if ($this->responsibleRole === AttendanceResponsibility::AdminStaff->value) {
            return collect();
        }

        if ($this->responsibleRole === AttendanceResponsibility::HomeroomTeacher->value) {
            return ClassSection::query()
                ->where('academic_year_id', $this->activeYear->id)
                ->where('homeroom_teacher_id', $this->teacherId)
                ->with('grade:id,name')
                ->get();
        }

        return CourseOffering::query()
            ->where('teacher_id', $this->teacherId)
            ->where('academic_year_id', $this->activeYear?->id)
            ->when($this->termId, fn($q) => $q->where('term_id', $this->termId))
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

        if (! $this->availableSections->pluck('id')->contains($this->classSectionId)) {
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
        if ($this->termId) {
            $months = GradebookMonth::where('term_id', $this->termId)
                ->orderBy('order')
                ->get(['start_date']);

            if ($months->isNotEmpty()) {
                return $months->mapWithKeys(function ($month) {
                    $monthNumber = (int) $month->start_date->format('m');
                    return [
                        $monthNumber => Carbon::create(null, $monthNumber, 1)->locale('ar')->monthName,
                    ];
                });
            }

            if ($this->activeTerm?->start_date && $this->activeTerm?->end_date) {
                $start = $this->activeTerm->start_date->copy()->startOfMonth();
                $end = $this->activeTerm->end_date->copy()->startOfMonth();
                $range = collect();

                while ($start->lte($end)) {
                    $monthNumber = (int) $start->format('m');
                    $range->put($monthNumber, Carbon::create(null, $monthNumber, 1)->locale('ar')->monthName);
                    $start->addMonth();
                }

                return $range;
            }
        }

        return collect(range(1, 12))->mapWithKeys(fn($m) => [
            $m => Carbon::create(null, $m, 1)->locale('ar')->monthName,
        ]);
    }

    /**
     * قائمة السنوات للاختيار
     */
    #[Computed]
    public function years()
    {
        if ($this->activeTerm) {
            $startYear = (int) $this->activeTerm->start_date->format('Y');
            $endYear = (int) $this->activeTerm->end_date->format('Y');
            return collect(range($startYear, $endYear));
        }

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

<?php

namespace App\Livewire\Admin\Promotion;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Results\Models\AnnualResult;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Grade\Models\Grade;

use App\Models\SystemSetting;
use App\Domains\Academic\Promotion\Services\AnnualResultService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AnnualResultsDashboard extends Component
{
    use WithPagination;

    public ?int $selectedYearId = null;
    public ?int $selectedGradeId = null;
    public ?int $selectedSectionId = null;
    public string $decisionFilter = 'all';
    public string $search = '';

    // Sorting
    public string $sortField = 'percentage';
    public string $sortDirection = 'desc';

    // Bulk Actions
    public array $selectedResults = [];
    public bool $selectAll = false;

    // Modals
    public bool $showProcessModal = false;
    public bool $showDetailsModal = false;
    public ?AnnualResult $selectedResult = null;

    public bool $processing = false;

    // Sidebar visibility
    public bool $showGradesSidebar = true;

    protected $queryString = ['selectedYearId', 'selectedGradeId', 'selectedSectionId', 'decisionFilter', 'search'];

    public function mount()
    {
        $this->selectedYearId = school()->activeYearId()
            ?? AcademicYear::latest('start_date')->first()?->id;
    }

    /**
     * حساب الخطوة الحالية بناءً على حالة البيانات
     */
    public function getCurrentStepProperty(): int
    {
        if (!$this->selectedYearId) {
            return 1;
        }

        $stats = $this->statistics;

        if (!$stats || $stats['total'] === 0) {
            return 1; // لم يتم تجميع النتائج بعد
        }

        if ($stats['pending'] > 0) {
            return 2; // يوجد قرارات معلقة - بحاجة لحساب القرارات
        }

        return 3; // جاهز للطباعة والترحيل
    }

    /**
     * حالة كل صف (مكتمل/جزئي/لم يبدأ)
     */
    public function getGradesStatusProperty(): array
    {
        if (!$this->selectedYearId) {
            return [];
        }

        $grades = Grade::with('stage')->get()->sortBy('stage.rank');
        $statuses = [];

        // 1. Get enrolled counts per grade
        $enrolledCounts = \App\Domains\Academic\Student\Models\StudentEnrollment::where('academic_year_id', $this->selectedYearId)
            ->where('status', 'active')
            ->selectRaw('grade_id, count(*) as count')
            ->groupBy('grade_id')
            ->pluck('count', 'grade_id');

        // 2. Get results stats per grade
        $resultsStats = AnnualResult::where('academic_year_id', $this->selectedYearId)
            ->selectRaw('grade_id, count(*) as total, sum(case when decision = "pending" then 1 else 0 end) as pending')
            ->groupBy('grade_id')
            ->get()
            ->keyBy('grade_id');

        foreach ($grades as $grade) {
            $enrolledCount = $enrolledCounts[$grade->id] ?? 0;
            $stats = $resultsStats[$grade->id] ?? null;
            $withResultsCount = $stats?->total ?? 0;
            $pendingCount = $stats?->pending ?? 0;

            $status = 'not_started';
            if ($withResultsCount > 0) {
                if ($withResultsCount >= $enrolledCount && $pendingCount === 0) {
                    $status = 'complete';
                } else {
                    $status = 'partial';
                }
            }

            $statuses[] = [
                'id' => $grade->id,
                'name' => $grade->name,
                'enrolled' => $enrolledCount,
                'with_results' => $withResultsCount,
                'status' => $status,
                'stats' => "{$withResultsCount}/{$enrolledCount}"
            ];
        }

        return $statuses;
    }

    /**
     * فلترة حسب الصف من الـ Sidebar
     */
    public function filterByGrade(?int $gradeId)
    {
        $this->selectedGradeId = $gradeId;
        $this->selectedSectionId = null;
        $this->resetPage();
    }


    public function getAcademicYearsProperty()
    {
        return AcademicYear::orderByDesc('start_date')->get();
    }

    public function getGradesProperty()
    {
        return Grade::with('stage')->get()->sortBy('stage.rank');
    }

    public function getClassSectionsProperty()
    {
        if (!$this->selectedGradeId) {
            return collect();
        }

        return ClassSection::where('grade_id', $this->selectedGradeId)
            ->when($this->selectedYearId, fn($q) => $q->where('academic_year_id', $this->selectedYearId))
            ->orderBy('name')
            ->get();
    }

    public function updatedSelectedGradeId()
    {
        $this->selectedSectionId = null;
        $this->resetPage();
    }

    public function getResultsProperty()
    {
        $query = AnnualResult::with(['student.currentClassSection', 'grade.stage', 'processedBy'])
            ->when($this->selectedYearId, fn($q) => $q->where('academic_year_id', $this->selectedYearId))
            ->when($this->selectedGradeId, fn($q) => $q->where('grade_id', $this->selectedGradeId))
            ->when($this->selectedSectionId, fn($q) => $q->whereHas(
                'student',
                fn($sq) => $sq->where('current_class_section_id', $this->selectedSectionId)
            ))
            ->when($this->decisionFilter !== 'all', fn($q) => $q->where('decision', $this->decisionFilter))
            ->when($this->search, fn($q) => $q->whereHas(
                'student',
                fn($sq) =>
                $sq->where('first_name_ar', 'like', "%{$this->search}%")
                    ->orWhere('family_name_ar', 'like', "%{$this->search}%")
                    ->orWhere('admission_number', 'like', "%{$this->search}%")
            ))
            ->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate(25);
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedResults = $this->results->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedResults = [];
        }
    }

    public function bulkUpdateDecision($decision)
    {
        if (empty($this->selectedResults)) {
            return;
        }

        AnnualResult::whereIn('id', $this->selectedResults)->update([
            'decision' => $decision,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        $this->selectedResults = [];
        $this->selectAll = false;
        $this->dispatch('notify', message: 'تم تحديث القرارات بنجاح', type: 'success');
    }

    public function exportCsv()
    {
        $results = AnnualResult::with(['student.currentClassSection', 'grade'])
            ->where('academic_year_id', $this->selectedYearId)
            ->when($this->selectedGradeId, fn($q) => $q->where('grade_id', $this->selectedGradeId))
            ->when($this->selectedSectionId, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('current_class_section_id', $this->selectedSectionId)))
            ->orderByDesc('percentage')
            ->get();

        $csvFileName = 'annual_results_' . date('Y-m-d_His') . '.csv';
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($results) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for proper Arabic support in Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($file, [
                'الرقم الأكاديمي',
                'اسم الطالب',
                'الصف',
                'الشعبة',
                'الترم الأول',
                'الترم الثاني',
                'المجموع الكلي',
                'الدرجة العظمى',
                'النسبة المئوية',
                'الترتيب',
                'التقدير',
                'عدد مواد الرسوب',
                'القرار'
            ]);

            $decisionLabels = [
                'pending' => 'قيد الانتظار',
                'pass' => 'ناجح',
                'conditional' => 'مُكمِّل',
                'fail' => 'راسب',
            ];

            foreach ($results as $row) {
                fputcsv($file, [
                    $row->student->admission_number,
                    $row->student->full_name_ar,
                    $row->grade->name ?? '-',
                    $row->student->currentClassSection?->name ?? '-',
                    $row->term1_total . '/' . $row->term1_max,
                    $row->term2_total . '/' . $row->term2_max,
                    $row->annual_total,
                    $row->annual_max,
                    number_format($row->percentage, 1) . '%',
                    $row->rank ?? '-',
                    $row->grade_label ?? '-',
                    $row->failed_count,
                    $decisionLabels[$row->decision] ?? $row->decision
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function viewDetails($id)
    {
        $this->selectedResult = AnnualResult::with(['student', 'grade', 'academicYear'])
            ->findOrFail($id);

        // Load term details if needed (يمكن جلبها من TermResult عند الحاجة)
        // For now, we'll just show the aggregated data available in AnnualResult

        $this->showDetailsModal = true;
    }

    public function getStatisticsProperty()
    {
        if (!$this->selectedYearId) {
            return null;
        }

        $year = AcademicYear::find($this->selectedYearId);
        return app(AnnualResultService::class)->getStatistics($year);
    }

    /**
     * تجميع نتائج الترمين
     */
    public function aggregateResults()
    {
        if (!$this->selectedYearId) {
            $this->dispatch('error', message: 'يرجى اختيار السنة الدراسية');
            return;
        }

        $this->processing = true;

        try {
            $year = AcademicYear::findOrFail($this->selectedYearId);
            $count = app(AnnualResultService::class)->aggregateTermResults($year);
            $this->dispatch('notify', message: "تم تجميع نتائج {$count} طالب بنجاح", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'حدث خطأ: ' . $e->getMessage());
        }

        $this->processing = false;
        $this->showProcessModal = false;
    }

    /**
     * حساب القرارات
     */
    public function calculateDecisions()
    {
        if (!$this->selectedYearId) {
            $this->dispatch('error', message: 'يرجى اختيار السنة الدراسية');
            return;
        }

        $this->processing = true;

        try {
            $year = AcademicYear::findOrFail($this->selectedYearId);
            $count = app(AnnualResultService::class)->calculateDecisions($year);
            $this->dispatch('notify', message: "تم حساب قرارات {$count} طالب بنجاح", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'حدث خطأ: ' . $e->getMessage());
        }

        $this->processing = false;
    }

    /**
     * تحديث قرار طالب (للمدير فقط)
     */
    public function updateDecision(int $resultId, string $newDecision)
    {
        // التحقق من صلاحية تعديل القرارات
        $this->authorize('edit annual results');

        $result = AnnualResult::findOrFail($resultId);
        $result->update([
            'decision' => $newDecision,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        $this->dispatch('notify', message: 'تم تحديث القرار بنجاح', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.promotion.annual-results-dashboard');
    }
}

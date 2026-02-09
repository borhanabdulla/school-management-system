<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Shared\Services\Dashboard\DashboardDrawerService;
use App\Domains\Shared\Services\Dashboard\MainDashboardDataService;
use Asantibanez\LivewireCharts\Facades\LivewireCharts;
use Asantibanez\LivewireCharts\Models\AreaChartModel;
use Asantibanez\LivewireCharts\Models\ColumnChartModel;
use Asantibanez\LivewireCharts\Models\LineChartModel;
use Asantibanez\LivewireCharts\Models\PieChartModel;

#[Layout('layouts.app')]
class MainDashboard extends Component
{
    public ?int $academicYearId = null;
    public ?int $gradeId = null;
    public ?int $termId = null;
    public string $range = '30';
    public string $focus = 'attendance';
    public string $alertSeverity = 'all';
    public bool $allYears = false;
    public bool $attendanceCompare = false;
    public bool $enrollmentCompare = false;
    public bool $financeCompare = false;
    public bool $loadHeatmap = false;
    public bool $loadPareto = false;
    public bool $loadAging = false;

    public bool $drawerOpen = false;
    public string $drawerType = '';
    public array $drawerContext = [];
    public array $drawerData = [];
    public ?string $drawerSignature = null;

    public bool $attendanceDropdownOpen = false;
    public ?string $attendanceDropdownDate = null;
    public array $attendanceDropdownData = [];
    public ?string $attendanceDropdownSignature = null;

    protected ?int $resolvedActiveYearId = null;
    protected ?int $resolvedActiveTermId = null;
    protected ?int $resolvedActiveTermYearId = null;

    public function mount(): void
    {
        $this->academicYearId = school()->activeYearId();
        if ($this->academicYearId) {
            $this->termId = $this->activeTermId($this->academicYearId);
        }
    }

    public function updatedAcademicYearId(): void
    {
        $this->termId = null;
        if ($this->academicYearId) {
            $this->termId = Term::query()
                ->where('academic_year_id', $this->academicYearId)
                ->where('status', TermStatus::Active)
                ->value('id');
        }
        $this->refreshDrawerData();
        $this->refreshAttendanceDropdownData();
        $this->resetLazyLoads();
    }

    public function updatedAllYears(): void
    {
        if ($this->allYears) {
            $this->academicYearId = null;
            $this->termId = null;
        } else {
            $this->academicYearId = $this->activeYearId();
            $this->termId = $this->activeTermId($this->academicYearId);
        }

        $this->refreshDrawerData();
        $this->refreshAttendanceDropdownData();
        $this->resetLazyLoads();
    }

    public function updatedGradeId(): void
    {
        $this->refreshDrawerData();
        $this->refreshAttendanceDropdownData();
        $this->resetLazyLoads();
    }

    public function updatedTermId(): void
    {
        $this->refreshDrawerData();
        $this->refreshAttendanceDropdownData();
        $this->resetLazyLoads();
    }

    public function updatedRange(): void
    {
        $this->refreshDrawerData();
        $this->refreshAttendanceDropdownData();
        $this->resetLazyLoads();
    }

    public function setFocus(string $focus): void
    {
        $this->focus = $focus;
    }

    public function loadHeatmap(): void
    {
        $this->loadHeatmap = true;
    }

    public function loadPareto(): void
    {
        $this->loadPareto = true;
    }

    public function loadAging(): void
    {
        $this->loadAging = true;
    }

    protected function resetLazyLoads(): void
    {
        $this->loadHeatmap = false;
        $this->loadPareto = false;
        $this->loadAging = false;
    }

    public function openDrawer(string $type, ?string $payload = null): void
    {
        $this->drawerType = $type;
        $this->drawerContext = [];
        if ($payload) {
            $this->drawerContext['payload'] = $payload;
        }

        $this->drawerSignature = $this->buildDrawerSignature($type, $payload);
        $this->drawerData = $this->buildDrawerData($type, $payload);
        $this->drawerOpen = true;
    }

    public function closeDrawer(): void
    {
        $this->drawerOpen = false;
    }

    public function openAttendanceDropdown(string $date): void
    {
        $this->attendanceDropdownDate = $date;
        $this->attendanceDropdownSignature = $this->buildAttendanceSignature($date);
        $this->attendanceDropdownData = $this->buildDrawerData('attendance-day', $date);
        $this->attendanceDropdownOpen = true;
    }

    public function closeAttendanceDropdown(): void
    {
        $this->attendanceDropdownOpen = false;
    }

    #[On('attendancePointClicked')]
    public function handleAttendancePointClicked(array $point): void
    {
        $date = data_get($point, 'extras.date');
        if (!$date) {
            return;
        }

        $this->openAttendanceDropdown($date);
    }

    #[On('invoiceSliceClicked')]
    public function handleInvoiceSliceClicked(array $slice): void
    {
        $status = data_get($slice, 'extras.status');
        $payload = $status ? "invoice-status:{$status}" : null;

        $this->openDrawer('finance', $payload);
    }

    #[On('enrollmentColumnClicked')]
    public function handleEnrollmentColumnClicked(array $column): void
    {
        $status = data_get($column, 'extras.status');
        $payload = $status ? "enrollment-status:{$status}" : null;

        $this->openDrawer('enrollment-status', $payload);
    }

    public function getAcademicYearsProperty()
    {
        return AcademicYear::query()
            ->orderByDesc('start_date')
            ->get();
    }

    public function getTermsProperty()
    {
        if ($this->allYears || !$this->academicYearId) {
            return collect();
        }

        return Term::query()
            ->where('academic_year_id', $this->academicYearId)
            ->orderBy('order_index')
            ->get();
    }

    public function getGradesProperty()
    {
        return Grade::query()
            ->orderBy('level_order')
            ->get();
    }

    public function getSelectedYearProperty(): ?AcademicYear
    {
        if (!$this->academicYearId) {
            return null;
        }

        return AcademicYear::find($this->academicYearId);
    }

    public function render()
    {
        return view('livewire.dashboard.main-dashboard', $this->buildDashboardData());
    }

    protected function buildDashboardData(): array
    {
        $filters = $this->dashboardFilters();
        $data = $this->dashboardDataService();

        $readinessSummary = $data->readinessSummary($this->selectedYear, $filters);
        $finance = $data->financeSummary($filters);
        $stats = $data->stats($filters, $readinessSummary, $finance);
        $gradeDistribution = $data->gradeDistribution($filters);
        $attendanceTrend = $data->attendanceTrend($filters);
        $attendanceHeatmap = [
            'weeks' => [],
            'labels' => [],
            'start' => null,
            'end' => null,
        ];
        $financeAging = [
            'items' => [],
            'max' => 1,
        ];
        $enrollmentTrend = $data->enrollmentTrend($filters);
        $financeTrend = $data->financeTrend($filters);
        $staffBreakdown = $data->staffBreakdown();
        $topTeachers = $data->topTeachers();
        $genderBreakdown = $data->genderBreakdown($filters);
        $upcomingEvents = $data->upcomingEvents($filters);
        $systemSnapshot = $data->systemSnapshot();
        $healthScore = $data->healthScore($stats, $finance);
        $termsSummary = $data->termsSummary($this->academicYearId);
        $alerts = $data->alerts($readinessSummary, $finance, $this->alertSeverity);
        $insights = $data->insights($stats, $finance);
        $readinessPareto = [
            'items' => [],
            'total' => 0,
            'max' => 1,
        ];
        $attendanceLineChart = $this->buildAttendanceSparklineChart($attendanceTrend);
        $enrollmentLineChart = $this->buildEnrollmentSparklineChart($enrollmentTrend);
        $financeLineChart = $this->buildFinanceSparklineChart($financeTrend);

        $attendanceBreakdown = ['items' => [], 'max' => 1, 'total' => 0];
        $attendanceFocusChart = null;
        $enrollmentBreakdown = ['items' => [], 'max' => 1];
        $enrollmentBreakdownChart = null;
        $invoiceBreakdown = ['items' => [], 'max' => 1];
        $invoiceBreakdownChart = null;

        if ($this->focus === 'attendance') {
            $attendanceBreakdown = $data->attendanceBreakdown($filters);
            $attendanceFocusChart = $this->buildAttendanceFocusChart($attendanceTrend);
        }

        if ($this->focus === 'enrollment') {
            $enrollmentBreakdown = $data->enrollmentBreakdown($filters);
            $enrollmentBreakdownChart = $this->buildEnrollmentBreakdownChart($enrollmentBreakdown);
        }

        if ($this->focus === 'finance') {
            $invoiceBreakdown = $data->invoiceBreakdown($filters);
            $invoiceBreakdownChart = $this->buildInvoiceBreakdownChart($invoiceBreakdown);
        }

        if ($this->loadHeatmap) {
            $attendanceHeatmap = $data->attendanceHeatmap($filters);
        }

        if ($this->loadPareto) {
            $readinessPareto = $data->readinessPareto($readinessSummary, $filters);
        }

        if ($this->loadAging) {
            $financeAging = $data->financeAgingBuckets($filters);
        }
        $colorMaps = config('ui.color_maps', []);
        $focusTabs = [
            'attendance' => 'الحضور',
            'finance' => 'المالية',
            'enrollment' => 'حالة الطلاب',
        ];
        $selectedTerm = $this->terms->firstWhere('id', $this->termId);
        $healthRing = $this->buildHealthRing($healthScore);

        return [
            'stats' => $stats,
            'gradeDistribution' => $gradeDistribution,
            'attendanceTrend' => $attendanceTrend,
            'attendanceHeatmap' => $attendanceHeatmap,
            'attendanceFocusChart' => $attendanceFocusChart,
            'financeAging' => $financeAging,
            'attendanceBreakdown' => $attendanceBreakdown,
            'enrollmentTrend' => $enrollmentTrend,
            'financeTrend' => $financeTrend,
            'enrollmentBreakdown' => $enrollmentBreakdown,
            'invoiceBreakdown' => $invoiceBreakdown,
            'enrollmentBreakdownChart' => $enrollmentBreakdownChart,
            'invoiceBreakdownChart' => $invoiceBreakdownChart,
            'staffBreakdown' => $staffBreakdown,
            'topTeachers' => $topTeachers,
            'genderBreakdown' => $genderBreakdown,
            'upcomingEvents' => $upcomingEvents,
            'systemSnapshot' => $systemSnapshot,
            'healthScore' => $healthScore,
            'alerts' => $alerts,
            'finance' => $finance,
            'termsSummary' => $termsSummary,
            'insights' => $insights,
            'readinessPareto' => $readinessPareto,
            'attendanceLineChart' => $attendanceLineChart,
            'enrollmentLineChart' => $enrollmentLineChart,
            'financeLineChart' => $financeLineChart,
            'colorMaps' => $colorMaps,
            'focusTabs' => $focusTabs,
            'selectedTerm' => $selectedTerm,
            'healthRing' => $healthRing,
        ];
    }

    protected function buildHealthRing(array $healthScore): string
    {
        $score = $healthScore['score'] ?? 0;
        $color = $healthScore['color'] ?? 'emerald';

        return match ($color) {
            'indigo' => "conic-gradient(#6366f1 0 {$score}%, rgba(148, 163, 184, 0.2) {$score}% 100%)",
            'amber' => "conic-gradient(#f59e0b 0 {$score}%, rgba(148, 163, 184, 0.2) {$score}% 100%)",
            'red' => "conic-gradient(#ef4444 0 {$score}%, rgba(148, 163, 184, 0.2) {$score}% 100%)",
            default => "conic-gradient(#22c55e 0 {$score}%, rgba(148, 163, 184, 0.2) {$score}% 100%)",
        };
    }

    protected function buildAttendanceSparklineChart(array $attendanceTrend): LineChartModel
    {
        $items = $attendanceTrend['items'] ?? [];
        $previousItems = $attendanceTrend['previous_items'] ?? [];
        $useCompare = $this->attendanceCompare && !empty($previousItems);

        $chart = $useCompare
            ? LivewireCharts::multiLineChartModel()
            : LivewireCharts::lineChartModel();

        $chart = $this->configureSparklineChart($chart, $useCompare ? ['#6ee7b7', '#a7f3d0'] : ['#6ee7b7'])
            ->withOnPointClickEvent('attendancePointClicked');

        foreach ($items as $item) {
            $payload = [
                'date' => $item['date'] ?? null,
                'tooltip' => ($item['rate'] ?? 0) . '%',
            ];
            if ($useCompare) {
                $chart->addSeriesPoint('الحالي', $item['label'] ?? '', $item['rate'] ?? 0, $payload);
                continue;
            }
            $chart->addPoint($item['label'] ?? '', $item['rate'] ?? 0, $payload);
        }

        if ($useCompare) {
            foreach ($previousItems as $item) {
                $chart->addSeriesPoint(
                    'السابق',
                    $item['label'] ?? '',
                    $item['rate'] ?? 0,
                    ['date' => $item['date'] ?? null, 'tooltip' => ($item['rate'] ?? 0) . '%']
                );
            }
        }

        return $chart;
    }

    protected function buildEnrollmentSparklineChart(array $enrollmentTrend): LineChartModel
    {
        $items = $enrollmentTrend['items'] ?? [];
        $previousItems = $enrollmentTrend['previous_items'] ?? [];
        $useCompare = $this->enrollmentCompare && !empty($previousItems);

        $chart = $useCompare
            ? LivewireCharts::multiLineChartModel()
            : LivewireCharts::lineChartModel();

        $chart = $this->configureSparklineChart($chart, $useCompare ? ['#a5b4fc', '#c7d2fe'] : ['#a5b4fc']);

        foreach ($items as $item) {
            $payload = ['tooltip' => (string) ($item['total'] ?? 0)];
            if ($useCompare) {
                $chart->addSeriesPoint('الحالي', $item['label'] ?? '', $item['total'] ?? 0, $payload);
                continue;
            }
            $chart->addPoint($item['label'] ?? '', $item['total'] ?? 0, $payload);
        }

        if ($useCompare) {
            foreach ($previousItems as $item) {
                $chart->addSeriesPoint(
                    'السابق',
                    $item['label'] ?? '',
                    $item['total'] ?? 0,
                    ['tooltip' => (string) ($item['total'] ?? 0)]
                );
            }
        }

        return $chart;
    }

    protected function buildFinanceSparklineChart(array $financeTrend): LineChartModel
    {
        $items = $financeTrend['items'] ?? [];
        $previousItems = $financeTrend['previous_items'] ?? [];
        $useCompare = $this->financeCompare && !empty($previousItems);

        $chart = $useCompare
            ? LivewireCharts::multiLineChartModel()
            : LivewireCharts::lineChartModel();

        $chart = $this->configureSparklineChart($chart, $useCompare ? ['#fdba74', '#fed7aa'] : ['#fdba74']);

        foreach ($items as $item) {
            $payload = ['tooltip' => number_format((float) ($item['total'] ?? 0), 0)];
            if ($useCompare) {
                $chart->addSeriesPoint('الحالي', $item['label'] ?? '', $item['total'] ?? 0, $payload);
                continue;
            }
            $chart->addPoint($item['label'] ?? '', $item['total'] ?? 0, $payload);
        }

        if ($useCompare) {
            foreach ($previousItems as $item) {
                $chart->addSeriesPoint(
                    'السابق',
                    $item['label'] ?? '',
                    $item['total'] ?? 0,
                    ['tooltip' => number_format((float) ($item['total'] ?? 0), 0)]
                );
            }
        }

        return $chart;
    }

    protected function configureSparklineChart(LineChartModel $chart, array $colors): LineChartModel
    {
        return $chart
            ->setSparklineEnabled(true)
            ->setAnimated(true)
            ->setDataLabelsEnabled(false)
            ->setXAxisVisible(false)
            ->setYAxisVisible(false)
            ->setSmoothCurve()
            ->setStrokeWidth(2)
            ->setColors($colors)
            ->setJsonConfig([
                'chart' => ['sparkline' => ['enabled' => true]],
                'grid' => ['padding' => ['left' => 0, 'right' => 0, 'top' => 0, 'bottom' => 0]],
                'tooltip' => ['theme' => 'dark'],
            ]);
    }

    protected function buildAttendanceFocusChart(array $attendanceTrend): AreaChartModel
    {
        $chart = LivewireCharts::areaChartModel()
            ->setAnimated(true)
            ->setColor('#a5b4fc')
            ->setSmoothCurve()
            ->setStrokeWidth(3)
            ->setDataLabelsEnabled(false)
            ->setXAxisVisible(false)
            ->setYAxisVisible(false)
            ->withOnPointClickEvent('attendancePointClicked')
            ->setJsonConfig([
                'yaxis' => [
                    'min' => 0,
                    'max' => 100,
                ],
                'grid' => [
                    'padding' => ['left' => 0, 'right' => 0, 'top' => 0, 'bottom' => 0],
                ],
                'tooltip' => ['theme' => 'dark'],
            ]);

        foreach ($attendanceTrend['items'] ?? [] as $item) {
            $rate = (int) ($item['rate'] ?? 0);
            $present = (int) ($item['present'] ?? 0);
            $total = (int) ($item['total'] ?? 0);
            $chart->addPoint(
                $item['label'] ?? '',
                $rate,
                [
                    'date' => $item['date'] ?? null,
                    'tooltip' => "{$rate}% • {$present}/{$total}",
                ]
            );
        }

        return $chart;
    }

    protected function dashboardDataService(): MainDashboardDataService
    {
        return app(MainDashboardDataService::class);
    }

    protected function dashboardFilters(): array
    {
        $yearId = $this->allYears ? null : ($this->academicYearId ?? $this->activeYearId());
        $termId = $this->allYears ? null : ($this->termId ?? $this->activeTermId($yearId));

        return [
            'academicYearId' => $yearId,
            'termId' => $termId,
            'gradeId' => $this->gradeId,
            'range' => $this->range,
        ];
    }

    protected function activeYearId(): ?int
    {
        if ($this->resolvedActiveYearId !== null) {
            return $this->resolvedActiveYearId;
        }

        $this->resolvedActiveYearId = school()->activeYearId();

        return $this->resolvedActiveYearId;
    }

    protected function activeTermId(?int $yearId): ?int
    {
        if (!$yearId) {
            return null;
        }

        if ($this->resolvedActiveTermYearId === $yearId && $this->resolvedActiveTermId !== null) {
            return $this->resolvedActiveTermId;
        }

        $this->resolvedActiveTermYearId = $yearId;
        if ($yearId === school()->activeYearId()) {
            $this->resolvedActiveTermId = school()->activeTermId();
        } else {
            $this->resolvedActiveTermId = Term::query()
                ->where('academic_year_id', $yearId)
                ->where('status', TermStatus::Active)
                ->value('id');
        }

        return $this->resolvedActiveTermId;
    }

    protected function drawerService(): DashboardDrawerService
    {
        return app(DashboardDrawerService::class);
    }

    protected function buildEnrollmentBreakdownChart(array $enrollmentBreakdown): ColumnChartModel
    {
        $chart = LivewireCharts::columnChartModel()
            ->setAnimated(true)
            ->setDataLabelsEnabled(false)
            ->setXAxisVisible(false)
            ->setYAxisVisible(false)
            ->setColumnWidth(55)
            ->setOpacity(0.9)
            ->withOnColumnClickEventName('enrollmentColumnClicked')
            ->setJsonConfig([
                'grid' => [
                    'padding' => ['left' => 0, 'right' => 0, 'top' => 0, 'bottom' => 0],
                ],
                'tooltip' => ['theme' => 'dark'],
            ]);

        foreach ($enrollmentBreakdown['items'] ?? [] as $item) {
            $color = $this->chartColor($item['color'] ?? 'slate');
            $chart->addColumn(
                $item['label'] ?? '',
                (int) ($item['total'] ?? 0),
                $color,
                [
                    'tooltip' => (string) ($item['total'] ?? 0),
                    'status' => $item['status'] ?? null,
                ]
            );
        }

        return $chart;
    }

    protected function buildInvoiceBreakdownChart(array $invoiceBreakdown): PieChartModel
    {
        $chart = LivewireCharts::pieChartModel()
            ->setAnimated(true)
            ->setDataLabelsEnabled(false)
            ->asDonut()
            ->withOnSliceClickEvent('invoiceSliceClicked')
            ->setJsonConfig([
                'legend' => ['show' => false],
                'tooltip' => ['theme' => 'dark'],
            ]);

        foreach ($invoiceBreakdown['items'] ?? [] as $item) {
            $color = $this->chartColor($item['color'] ?? 'slate');
            $chart->addSlice(
                $item['label'] ?? '',
                (int) ($item['total'] ?? 0),
                $color,
                [
                    'tooltip' => (string) ($item['total'] ?? 0),
                    'status' => $item['status'] ?? null,
                ]
            );
        }

        return $chart;
    }

    protected function chartColor(string $key): string
    {
        $map = config('ui.chart_colors', []);

        return $map[$key] ?? '#64748b';
    }

    protected function buildDrawerData(string $type, ?string $payload = null): array
    {
        return $this->drawerService()->buildDrawerData(
            $type,
            $payload,
            $this->dashboardFilters(),
            $this->selectedYear
        );
    }

    protected function refreshDrawerData(): void
    {
        if (!$this->drawerOpen || $this->drawerType === '') {
            return;
        }

        $payload = $this->drawerContext['payload'] ?? null;
        $signature = $this->buildDrawerSignature($this->drawerType, $payload);
        if ($signature === $this->drawerSignature) {
            return;
        }

        $this->drawerSignature = $signature;
        $this->drawerData = $this->buildDrawerData($this->drawerType, $payload);
    }

    protected function refreshAttendanceDropdownData(): void
    {
        if (!$this->attendanceDropdownOpen || !$this->attendanceDropdownDate) {
            return;
        }

        $signature = $this->buildAttendanceSignature($this->attendanceDropdownDate);
        if ($signature === $this->attendanceDropdownSignature) {
            return;
        }

        $this->attendanceDropdownSignature = $signature;
        $this->attendanceDropdownData = $this->buildDrawerData('attendance-day', $this->attendanceDropdownDate);
    }

    protected function buildDrawerSignature(string $type, ?string $payload = null): string
    {
        $filters = $this->dashboardFilters();

        return implode('|', [
            $type,
            $payload ?? 'none',
            $filters['academicYearId'] ?? 'all',
            $filters['termId'] ?? 'all',
            $filters['gradeId'] ?? 'all',
            $filters['range'] ?? 'all',
        ]);
    }

    protected function buildAttendanceSignature(string $date): string
    {
        return $this->buildDrawerSignature('attendance-day', $date);
    }
}

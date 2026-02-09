<?php

namespace App\Livewire\HR;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Domains\HR\Leave\Models\LeaveRequest;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Shared\Services\HRDashboardService;
use App\Domains\HR\Substitution\Services\SubstitutionService;
use Carbon\Carbon;

#[Layout('layouts.app')]
class HRDashboard extends Component
{
    public $stats = [];
    public $alerts = [];
    public $weekHeatmap = [];
    public $affectedClasses = [];
    public $selectedDate;
    public $pendingRequests = [];
    public $calendarDate;
    public $calendarDays = [];

    // Substitution Modal State
    public bool $showSubstituteModal = false;
    #[Locked]
    public ?int $selectedTimetableId = null;
    #[Locked]
    public ?int $selectedTeacherId = null;
    #[Locked]
    public ?int $selectedLeaveId = null;
    public array $suggestedSubstitutes = [];
    public array $substituteFilters = [
        'same_specialization' => true,
        'ignore_busy' => false,
        'max_daily_load' => 6,
    ];

    protected HRDashboardService $dashboardService;
    protected SubstitutionService $substitutionService;

    public function boot(HRDashboardService $dashboardService, SubstitutionService $substitutionService)
    {
        $this->dashboardService = $dashboardService;
        $this->substitutionService = $substitutionService;
    }

    public function mount()
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->calendarDate = now()->startOfMonth();
        $this->loadData();
    }

    public function loadData()
    {
        $this->stats = $this->dashboardService->getEnhancedStats();
        $this->loadAlerts();
        $this->loadWeekHeatmap();
        $this->loadAffectedClasses();
        $this->loadPendingRequests();
        $this->buildCalendar();
    }

    protected function loadAlerts()
    {
        $this->alerts = $this->dashboardService->getActiveAlerts()
            ->reject(fn($alert) => in_array($alert['id'] ?? null, session('dismissed_alerts', [])))
            ->values()
            ->all();
    }


    protected function loadWeekHeatmap()
    {
        $this->weekHeatmap = $this->dashboardService->getWeekHeatmap();
    }

    protected function loadAffectedClasses()
    {
        $date = Carbon::parse($this->selectedDate);
        $rawData = $this->dashboardService->getAffectedClasses($date); // جلب الحصص المتاثره 

        // Transform the collection to the expected format
        $byTeacher = $rawData->groupBy('teacher_id')->map(function ($classes, $teacherId) {
            $first = $classes->first(); //ارجاع اول 
            return [
                'teacher_id' => $teacherId,
                'teacher_name' => $first['teacher_name'] ?? 'غير محدد',
                'classes' => $classes->toArray(),
                'classes_count' => $classes->count(),
                'covered_count' => $classes->where('has_substitute', true)->count(), //الحصص التي لها معلم بديل
            ];
        })->values()->toArray();

        $this->affectedClasses = [
            'total_affected' => $rawData->count(),
            'date_formatted' => $date->translatedFormat('l، j F Y'),
            'teachers_count' => count($byTeacher),
            'by_teacher' => $byTeacher,
        ];
    }

    protected function loadPendingRequests()
    {
        $this->pendingRequests = LeaveRequest::with(['staff', 'leaveType'])
            ->where('status', 'pending')->orderBy('created_at', 'desc')->take(5)->get();
    }

    protected function buildCalendar()
    {
        $this->calendarDays = $this->dashboardService->getCalendarData($this->calendarDate);
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->loadWeekHeatmap();
        $this->loadAffectedClasses();
        $this->buildCalendar();
    }

    public function previousMonth()
    {
        $this->calendarDate = $this->calendarDate->copy()->subMonth();
        $this->buildCalendar();
    }

    public function nextMonth()
    {
        $this->calendarDate = $this->calendarDate->copy()->addMonth();
        $this->buildCalendar();
    }


    // ==================== Substitution Methods ====================

    public function openSubstituteModal(int $timetableId, int $teacherId, ?int $leaveId = null)
    {
        $this->selectedTimetableId = $timetableId;
        $this->selectedTeacherId = $teacherId;
        $this->selectedLeaveId = $leaveId;

        // Reset filters to default when opening
        $this->substituteFilters = [
            'same_specialization' => true,
            'ignore_busy' => false,
            'max_daily_load' => 6,
        ];

        $this->loadSuggestedSubstitutes();

        $this->showSubstituteModal = true;
    }

    public function updatedSubstituteFilters()
    {
        $this->loadSuggestedSubstitutes();
    }

    public function loadSuggestedSubstitutes()
    {
        if (!$this->selectedTimetableId)
            return;

        $date = Carbon::parse($this->selectedDate);
        $this->suggestedSubstitutes = $this->substitutionService
            ->getSuggestedSubstitutes(
                $this->selectedTimetableId,
                $date,
                $this->substituteFilters
            )
            ->take(10) // Increased limit since we have filters
            ->toArray();
    }

    public function closeSubstituteModal()
    {
        $this->showSubstituteModal = false;
        $this->selectedTimetableId = null;
        $this->selectedTeacherId = null;
        $this->selectedLeaveId = null;
        $this->suggestedSubstitutes = [];
    }

    public function assignSubstitute(int $substituteTeacherId)
    {
        if (!$this->selectedTimetableId || !$this->selectedTeacherId) {
            return;
        }

        $date = Carbon::parse($this->selectedDate);

        $this->substitutionService->assignSubstitute(
            $this->selectedTimetableId,
            $substituteTeacherId,
            $this->selectedTeacherId,
            $date,
            $this->selectedLeaveId,
            false, // is_paid - يمكن إضافة خيار للمستخدم
            auth()->id()
        );

        $this->closeSubstituteModal();
        $this->loadAffectedClasses();

        $this->dispatch('notify', message: 'تم تعيين البديل بنجاح.', type: 'success');
    }

    public function dismissAlert($alertId)
    {
        session()->push('dismissed_alerts', $alertId);
        $this->loadAlerts();
    }

    public function render()
    {
        return view('livewire.hr.hr-dashboard');
    }
}

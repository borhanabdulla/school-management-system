<?php

namespace App\Livewire\HR;

use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Staff\Models\StaffAttendance;
use App\Domains\HR\WorkShift\Models\WorkShift;
use App\Domains\HR\Attendance\Services\AttendanceService;
use App\Domains\HR\Substitution\Services\SubstitutionService;
use App\Domains\Academic\Calendar\Services\SchoolCalendarService;
use Livewire\Component;
use Carbon\Carbon;

class StaffAttendanceDashboard extends Component
{
    // Filters
    public string $date;
    public ?int $shiftId = null;
    public string $statusFilter = '';
    public string $search = ''; // Added search

    // Attendance Data
    public array $attendanceData = [];
    public bool $isHoliday = false;
    public string $holidayName = '';

    // Stats
    public array $stats = [];

    // Alerts
    public array $alerts = [];

    // Edit Modal
    public bool $showEditModal = false;
    public ?int $editingStaffId = null;
    public string $editCheckIn = '';
    public string $editCheckOut = '';
    public string $editRemarks = '';

    // Substitution Modal
    public bool $showSubstitutionModal = false;
    public ?Staff $absentTeacher = null;

    protected AttendanceService $attendanceService;
    protected SubstitutionService $substitutionService;
    protected SchoolCalendarService $calendarService;

    public function boot(
        AttendanceService $attendanceService,
        SubstitutionService $substitutionService,
        SchoolCalendarService $calendarService
    )
    {
        $this->attendanceService = $attendanceService;
        $this->substitutionService = $substitutionService;
        $this->calendarService = $calendarService;
    }

    public function mount()
    {
        $this->date = now()->toDateString();
        $this->loadAttendanceData();
    }

    public function updatedDate()
    {
        $this->loadAttendanceData();
    }

    public function updatedShiftId()
    {
        $this->loadAttendanceData();
    }

    // ... (existing code)

    public function updatedSearch()
    {
        $this->loadAttendanceData();
    }

    public function loadAttendanceData(): void
    {
        $result = $this->attendanceService->getAttendanceSheet($this->date, $this->shiftId);

        // Check if holiday
        if (is_array($result) && ($result['is_holiday'] ?? false)) {
            $this->isHoliday = true;
            $this->holidayName = $result['holiday_name'] ?? 'عطلة رسمية';
            $this->attendanceData = $result['staff']?->toArray() ?? [];
        } else {
            $this->isHoliday = false;
            $this->holidayName = '';
            $this->attendanceData = $result->toArray();
        }

        // Apply Search Filter
        if ($this->search) {
            $this->attendanceData = collect($this->attendanceData)->filter(function ($item) {
                $fullName = $item['staff']['first_name'] . ' ' . $item['staff']['last_name'];
                return str_contains($fullName, $this->search) ||
                    str_contains($item['staff']['employee_number'], $this->search);
            })->values()->toArray();
        }

        // Load stats
        $this->stats = $this->attendanceService->getDailyStats($this->date);
    }

    // ... (existing code)

    public function markPresent(int $staffId): void
    {
        // Find the staff's shift start time
        $item = collect($this->attendanceData)->firstWhere('staff_id', $staffId);

        if ($item && $item['shift']) {
            // Use shift start time
            $checkIn = Carbon::parse($item['shift']['start_time'])->format('H:i');
        } else {
            // Fallback to current time if no shift
            $checkIn = now()->format('H:i');
        }

        // Update local state
        $index = collect($this->attendanceData)->search(fn($i) => $i['staff_id'] === $staffId);
        if ($index !== false) {
            $this->attendanceData[$index]['check_in'] = $checkIn;
        }

        $this->saveAttendance($staffId, $checkIn, null);
    }

    public function markAbsent(int $staffId): void
    {
        $this->saveAttendance($staffId, null, null);

        // Check if teacher for substitution modal
        $item = collect($this->attendanceData)->firstWhere('staff_id', $staffId);
        if ($item && ($item['staff']['teacher'] ?? false)) {
            // Check if has classes (reusing logic or calling service)
            // For simplicity, we just show modal if it's a teacher. 
            // Or we can check classes first.
            $this->checkTeacherSubstitution($item['staff']);
        }
    }

    private function saveAttendance(int $staffId, ?string $checkIn, ?string $checkOut, ?string $remarks = null): void
    {
        try {
            $this->attendanceService->saveAttendance(
                $staffId,
                $this->date,
                $checkIn,
                $checkOut,
                auth()->id(),
                $remarks
            );

            $this->loadAttendanceData();
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    private function checkTeacherSubstitution(array $staffData): void
    {
        // We need the Staff model instance
        $staff = Staff::find($staffData['id']);
        if (!$staff || !$staff->teacher)
            return;

        $hasClasses = $this->substitutionService
            ->teacherHasClassesOnDate($staff->teacher->id, Carbon::parse($this->date));

        if ($hasClasses) {
            $this->absentTeacher = $staff;
            $this->showSubstitutionModal = true;
        }
    }

    public function closeSubstitutionModal(): void
    {
        $this->showSubstitutionModal = false;
        $this->absentTeacher = null;
    }

    public function goToSubstitution(): void
    {
        // Redirect to substitution page (placeholder for now)
        // return redirect()->route('academic.substitution.create', ['teacher_id' => $this->absentTeacher->teacher->id, 'date' => $this->date]);

        // For now just close modal
        $this->closeSubstitutionModal();
        $this->dispatch('notify', 'سيتم توجيهك لصفحة الاحتياط (قيد التطوير)');
    }

    public function openEditModal(int $staffId): void
    {
        $this->editingStaffId = $staffId;

        // Find existing record
        $item = collect($this->attendanceData)->firstWhere('staff_id', $staffId);

        $this->editCheckIn = $item['check_in'] ?? '';
        $this->editCheckOut = $item['check_out'] ?? '';
        $this->editRemarks = $item['record']['remarks'] ?? '';

        $this->showEditModal = true;
    }

    public function saveEditedAttendance(): void
    {
        $this->saveAttendance(
            $this->editingStaffId,
            $this->editCheckIn ?: null,
            $this->editCheckOut ?: null,
            $this->editRemarks ?: null
        );

        $this->showEditModal = false;
        $this->editingStaffId = null;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingStaffId = null;
    }

    public function previousDay(): void
    {
        $this->date = Carbon::parse($this->date)->subDay()->toDateString();
        $this->loadAttendanceData();
    }

    public function nextDay(): void
    {
        $newDate = Carbon::parse($this->date)->addDay();
        if ($newDate->lte(now())) {
            $this->date = $newDate->toDateString();
            $this->loadAttendanceData();
        }
    }

    public function goToToday(): void
    {
        $this->date = now()->toDateString();
        $this->loadAttendanceData();
    }

    public function render()
    {
        $shifts = WorkShift::active()->orderBy('name')->get();

        // Filter by status if needed
        $filteredData = collect($this->attendanceData);
        if ($this->statusFilter) {
            $filteredData = $filteredData->filter(fn($item) => $item['status'] === $this->statusFilter);
        }

        return view('livewire.hr.staff-attendance-dashboard', [
            'shifts' => $shifts,
            'filteredData' => $filteredData,
            'dateDisplay' => Carbon::parse($this->date)->translatedFormat('l j F Y'),
            'isToday' => $this->date === now()->toDateString(),
        ])->layout('layouts.app');
    }
}

<?php

namespace App\Livewire\HR;

use App\Domains\HR\Staff\Actions\DeleteStaffAction;
use App\Domains\HR\Staff\Actions\UpdateStaffAction;
use App\Domains\HR\Enums\StaffRole;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\HR\WorkShift\Models\WorkShift;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * واجهة تعديل بيانات موظف
 */
class StaffEdit extends Component
{
    public Staff $staff;

    // ============================================
    // البيانات الشخصية
    // ============================================
    #[Validate('required|string|min:2|max:50')]
    public string $first_name = '';

    #[Validate('required|string|min:2|max:50')]
    public string $last_name = '';

    #[Validate('nullable|string|min:8|max:15')]
    public ?string $phone = null;

    // ============================================
    // بيانات الوظيفة
    // ============================================
    #[Validate('required|string')]
    public string $role = '';

    #[Validate('required|string|max:100')]
    public string $job_title = '';

    #[Validate('nullable|exists:work_shifts,id')]
    public ?int $work_shift_id = null;

    #[Validate('required|in:full_time,part_time,contractor')]
    public string $employment_type = 'full_time';

    #[Validate('required|date')]
    public string $joining_date = '';

    #[Validate('required|in:active,on_leave,terminated')]
    public string $status = 'active';

    // ============================================
    // بيانات المعلم
    // ============================================
    #[Validate('nullable|string|max:100')]
    public ?string $specialization = null;

    #[Validate('nullable|integer|min:1|max:40')]
    public ?int $max_weekly_classes = 24;

    // ============================================
    // بيانات مساعدة للـ View
    // ============================================
    public array $workShifts = [];
    public array $existingSpecializations = [];
    public bool $showDeleteModal = false;
    public array $deleteCheckResult = [];

    public function mount(Staff $staff): void
    {
        $this->staff = $staff->load(['user', 'teacher', 'workShift']);

        // Fill form with existing data
        $this->first_name = $staff->first_name;
        $this->last_name = $staff->last_name;
        $this->phone = $staff->phone;
        $this->job_title = $staff->job_title ?? '';
        $this->work_shift_id = $staff->work_shift_id;
        $this->employment_type = $staff->employment_type ?? 'full_time';
        $this->joining_date = $staff->joining_date?->format('Y-m-d') ?? '';
        $this->status = $staff->status ?? 'active';

        // Determine role from user roles or staff type
        if ($staff->user && $staff->user->roles->isNotEmpty()) {
            $this->role = $staff->user->roles->first()->name;
        } elseif ($staff->teacher) {
            $this->role = 'teacher';
        } else {
            $this->role = 'other';
        }

        // Teacher specific data
        if ($staff->teacher) {
            $this->specialization = $staff->teacher->specialization;
            $this->max_weekly_classes = $staff->teacher->max_weekly_classes;
        }

        $this->loadWorkShifts();
        $this->loadSpecializations();
    }

    protected function loadWorkShifts(): void
    {
        $this->workShifts = WorkShift::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'start_time', 'end_time'])
            ->map(fn($shift) => [
                'id' => $shift->id,
                'name' => $shift->name,
                'time' => \Carbon\Carbon::parse($shift->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($shift->end_time)->format('H:i'),
            ])
            ->toArray();
    }

    protected function loadSpecializations(): void
    {
        $this->existingSpecializations = Teacher::whereNotNull('specialization')
            ->distinct()
            ->pluck('specialization')
            ->filter()
            ->values()
            ->toArray();
    }

    public function getShowTeacherFieldsProperty(): bool
    {
        return $this->role === StaffRole::Teacher->value;
    }

    public function getRolesProperty(): array
    {
        return StaffRole::options();
    }

    public function save(UpdateStaffAction $action): mixed
    {
        $this->validate();

        try {
            $action->execute($this->staff, [
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'phone' => $this->phone,
                'job_title' => $this->job_title,
                'work_shift_id' => $this->work_shift_id,
                'employment_type' => $this->employment_type,
                'joining_date' => $this->joining_date,
                'status' => $this->status,
                'role' => $this->role,
                'specialization' => $this->specialization,
                'max_weekly_classes' => $this->max_weekly_classes,
            ]);

            $this->dispatch('notify', message: 'تم تحديث بيانات الموظف بنجاح.');
            return redirect()->route('hr.staff.index');
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'حدث خطأ: ' . $e->getMessage());
            return null;
        }
    }

    public function checkDelete(DeleteStaffAction $action): void
    {
        $this->deleteCheckResult = $action->canDelete($this->staff);
        $this->showDeleteModal = true;
    }

    public function delete(DeleteStaffAction $action): mixed
    {
        try {
            $action->execute($this->staff, force: true);
            $this->dispatch('notify', message: 'تم حذف الموظف بنجاح.');
            return redirect()->route('hr.staff.index');
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'حدث خطأ أثناء الحذف: ' . $e->getMessage());
            $this->showDeleteModal = false;
            return null;
        }
    }

    public function deactivate(DeleteStaffAction $action): void
    {
        try {
            $action->deactivate($this->staff);
            $this->status = 'terminated';
            $this->showDeleteModal = false;
            $this->dispatch('notify', message: 'تم تعطيل الموظف بنجاح.');
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.hr.staff-edit')
            ->layout('layouts.app');
    }
}

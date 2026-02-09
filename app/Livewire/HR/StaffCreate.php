<?php

namespace App\Livewire\HR;

use App\Domains\HR\Staff\Actions\CreateStaffAction;
use App\Data\Staff\StaffOnboardingData;
use App\Domains\HR\Enums\StaffRole;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\HR\WorkShift\Models\WorkShift;
use Livewire\Attributes\Validate;
use Livewire\Component;
use App\Livewire\Forms\HR\StaffCreateForm;

/**
 * واجهة إضافة موظف جديد
 * تدعم جميع أنواع الموظفين مع حقول ديناميكية
 */
class StaffCreate extends Component
{
    public StaffCreateForm $form;

    // ============================================
    // بيانات مساعدة للـ View
    // ============================================
    public array $workShifts = [];
    public array $existingSpecializations = [];

    public function mount(): void
    {
        $this->form->joining_date = now()->format('Y-m-d');
        $this->loadWorkShifts();
        $this->loadSpecializations();
    }

    /**
     * تحميل فترات الدوام المتاحة
     */
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

    /**
     * تحميل التخصصات الموجودة (للـ Autocomplete)
     */
    protected function loadSpecializations(): void
    {
        $this->existingSpecializations = Teacher::whereNotNull('specialization')
            ->distinct()
            ->pluck('specialization')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * هل الدور المختار يتطلب حقول المعلم؟
     */
    public function getShowTeacherFieldsProperty(): bool
    {
        return $this->form->role === StaffRole::Teacher->value;
    }

    /**
     * قائمة الأدوار للـ Select
     */
    public function getRolesProperty(): array
    {
        return StaffRole::options();
    }

    /**
     * حفظ الموظف والعودة للقائمة
     */
    public function save(CreateStaffAction $action): mixed
    {
        try {
            $data = $this->form->prepareData();
            $action->execute($data);

            $this->dispatch('notify', message: 'تم إضافة الموظف بنجاح.');
            return redirect()->route('hr.staff.index');
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'حدث خطأ: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * حفظ وإضافة آخر
     */
    public function saveAndCreateAnother(CreateStaffAction $action): void
    {
        try {
            $data = $this->form->prepareData();
            $action->execute($data);

            $this->dispatch('notify', message: 'تم إضافة الموظف بنجاح. يمكنك إضافة موظف آخر.');
            $this->form->reset();
            $this->form->joining_date = now()->format('Y-m-d');
            $this->loadSpecializations();
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.hr.staff-create')
            ->layout('layouts.app');
    }
}

<?php

namespace App\Livewire\HR;

use App\Domains\HR\Staff\Models\Staff;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * قائمة الموظفين
 * عرض جميع الموظفين مع إمكانية البحث والفلترة
 */
class StaffDirectory extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $employment_type = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public $staffToDelete = null;
    public bool $showDeleteModal = false;
    public array $deleteCheckResult = [];

    public function confirmDelete(Staff $staff, \App\Domains\HR\Staff\Actions\DeleteStaffAction $action): void
    {
        $this->staffToDelete = $staff;
        $this->deleteCheckResult = $action->canDelete($staff);
        $this->showDeleteModal = true;
    }

    public function delete(\App\Domains\HR\Staff\Actions\DeleteStaffAction $action): void
    {
        if (!$this->staffToDelete)
            return;

        try {
            $action->execute($this->staffToDelete, force: true);
            $this->dispatch('notify', message: 'تم حذف الموظف بنجاح.');
            $this->showDeleteModal = false;
            $this->staffToDelete = null;
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'حدث خطأ أثناء الحذف: ' . $e->getMessage());
            $this->showDeleteModal = false;
        }
    }

    public function deactivate(\App\Domains\HR\Staff\Actions\DeleteStaffAction $action): void
    {
        if (!$this->staffToDelete)
            return;

        try {
            $action->deactivate($this->staffToDelete);
            $this->dispatch('notify', message: 'تم تعطيل الموظف بنجاح.');
            $this->showDeleteModal = false;
            $this->staffToDelete = null;
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $staff = Staff::query()
            ->with(['workShift', 'teacher', 'user'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhere('employee_number', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%")
                        ->orWhereHas('user', function ($uq) {
                            $uq->where('email', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->employment_type, fn($q) => $q->where('employment_type', $this->employment_type))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.hr.staff-directory', [
            'staffList' => $staff,
        ])->layout('layouts.app');
    }
}

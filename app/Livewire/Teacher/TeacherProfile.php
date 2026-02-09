<?php

namespace App\Livewire\Teacher;

use Livewire\Component;
use App\Domains\HR\Teacher\Models\Teacher;
use Illuminate\Support\Facades\Storage;

class TeacherProfile extends Component
{
    public $teacherId;
    public $teacher;
    public bool $canDelete = false;
    public array $deleteBlockers = [];

    public function mount($teacherId)
    {
        $this->teacherId = $teacherId;
        $this->teacher = Teacher::with(['staff.user', 'courseOfferings.subject', 'courseOfferings.classSection.grade'])
            ->findOrFail($teacherId);
        
        $this->checkCanDelete();
    }

    protected function checkCanDelete(): void
    {
        $this->deleteBlockers = [];

        // Rule 1: Cannot delete if has active course offerings
        if ($this->teacher->courseOfferings->count() > 0) {
            $this->deleteBlockers[] = 'لديه حصص دراسية مسندة (' . $this->teacher->courseOfferings->count() . ' حصة)';
        }

        // Rule 2: Could add more rules here (e.g., pending grades, etc.)
        
        $this->canDelete = empty($this->deleteBlockers);
    }

    public function delete()
    {
        if (!$this->canDelete) {
            $this->dispatch('error', message: 'لا يمكن حذف هذا المعلم: ' . implode('، ', $this->deleteBlockers));
            return;
        }

        try {
            // Delete profile photo if exists
            if ($this->teacher->staff->user->profile_photo_path ?? null) {
                Storage::disk('public')->delete($this->teacher->staff->user->profile_photo_path);
            }

            // Delete related records (cascading)
            $staff = $this->teacher->staff;
            $user = $staff->user;

            $this->teacher->delete();
            $staff->delete();
            if ($user) {
                $user->delete();
            }

            $this->dispatch('notify', message: 'تم حذف المعلم بنجاح.');
            return redirect()->route('teachers.index');
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'حدث خطأ أثناء الحذف: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.teacher.teacher-profile');
    }
}

<?php

namespace App\Livewire\Admin\Access;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserRoleAssigner extends Component
{
    public $user;
    public $userId;
    public $availableRoles;
    public $selectedRoles = [];

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->user = User::findOrFail($userId);
        $this->availableRoles = Role::withCount('permissions')->get();
        $this->selectedRoles = $this->user->roles->pluck('name')->toArray();
    }

    public function updateRoles()
    {
        $this->user->syncRoles($this->selectedRoles);
        $this->dispatch('notify', 'تم تحديث أدوار المستخدم بنجاح!');
    }

    public function render()
    {
        return view('livewire.admin.access.user-role-assigner', [
            'roleMap' => $this->getRoleMap(),
        ]);
    }

    protected function getRoleMap(): array
    {
        return config('access.role_labels', []);
    }
}

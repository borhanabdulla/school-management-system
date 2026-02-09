<?php

namespace App\Livewire\Admin\Access;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleManager extends Component
{
    public function delete($id)
    {
        $role = Role::findOrFail($id);

        if ($this->isStaticRole($role->name)) {
            $this->dispatch('notify', 'لا يمكن حذف دور أساسي في النظام!', 'error');
            return;
        }

        if ($role->users()->count() > 0) {
            $this->dispatch('notify', 'لا يمكن حذف دور مرتبط بمستخدمين!', 'error');
            return;
        }

        $role->delete();
        $this->dispatch('notify', 'تم حذف الدور بنجاح!');
    }

    public function render()
    {
        $roles = Role::with('permissions')->withCount('users')->get();

        return view('livewire.admin.access.role-manager', [
            'roles' => $roles,
            'permissionMap' => $this->getPermissionMap(),
            'roleMap' => $this->getRoleMap(),
            'staticRoles' => $this->getStaticRoles(),
        ])->layout('layouts.app');
    }

    protected function getPermissionMap()
    {
        return config('access.permission_labels', []);
    }

    protected function getRoleMap()
    {
        return config('access.role_labels', []);
    }

    protected function getStaticRoles(): array
    {
        return config('access.static_roles', ['Super Admin', 'Teacher', 'Student', 'Parent']);
    }

    protected function isStaticRole(string $name): bool
    {
        return in_array($name, $this->getStaticRoles(), true);
    }
}

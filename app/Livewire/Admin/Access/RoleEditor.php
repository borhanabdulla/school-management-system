<?php

namespace App\Livewire\Admin\Access;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleEditor extends Component
{
    #[Locked]
    public $role;
    public $name;
    public $selectedPermissions = [];
    public $isEditing = false;

    protected $rules = [
        'name' => 'required|string|min:3|unique:roles,name',
        'selectedPermissions' => 'array',
    ];

    public $activeTab = 'permissions';
    public $searchMember = '';

    public function mount($role = null)
    {
        if ($role) {
            $this->role = Role::findOrFail($role);
            $this->name = $this->role->name;
            // Only load permissions if role exists
            $this->selectedPermissions = $this->role->permissions->pluck('name')->toArray();
            $this->isEditing = true;
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function addMember($userId)
    {
        if (!$this->isEditing)
            return;

        $user = \App\Models\User::find($userId);
        if ($user) {
            $user->assignRole($this->role->name);
            $this->dispatch('notify', message: 'تم إضافة العضو بنجاح!', type: 'success');
        }
    }

    public function removeMember($userId)
    {
        if (!$this->isEditing)
            return;

        $user = \App\Models\User::find($userId);
        if ($user) {
            $user->removeRole($this->role->name);
            $this->dispatch('notify', message: 'تم إزالة العضو بنجاح!', type: 'success');
        }
    }

    public function save()
    {
        // Validation logic
        $rules = $this->rules;
        if ($this->isEditing) {
            $rules['name'] = 'required|string|min:3|unique:roles,name,' . $this->role->id;
        }

        // Prevent renaming static roles
        if ($this->isEditing && $this->isStaticRole($this->role->name)) {
            unset($rules['name']);
        }

        $this->validate($rules);

        if ($this->isEditing) {
            // Update existing role
            if (!$this->isStaticRole($this->role->name)) {
                $this->role->name = $this->name;
            }
            $this->role->save();
            $this->role->syncPermissions($this->selectedPermissions);

            $this->dispatch('notify', message: 'تم تحديث الدور بنجاح!', type: 'success');
        } else {
            // Create new role
            $role = Role::create(['name' => $this->name, 'guard_name' => 'web']);
            $role->syncPermissions($this->selectedPermissions);

            // Redirect to edit page to add members
            return redirect()->route('admin.access.roles.edit', $role->id);
        }

        return redirect()->route('admin.access.roles.index');
    }

    public function render()
    {
        $permissions = Permission::all();

        // Group permissions by module (prefix)
        $groupedPermissions = $permissions->groupBy(function ($perm) {
            $parts = explode('.', $perm->name);
            return count($parts) > 1 ? strtolower($parts[0]) : 'other';
        })->sortKeys();

        $members = null;
        $foundUsers = collect();

        if ($this->isEditing) {
            // Helper for members tab
            $members = $this->role->users()
                ->when($this->searchMember, fn($q) => $q->where(function ($sub) {
                    $sub->where('name', 'like', '%' . $this->searchMember . '%')
                        ->orWhere('email', 'like', '%' . $this->searchMember . '%');
                }))
                ->paginate(10);

            // Helper for "Add Member" search
            if (strlen($this->searchMember) > 2) {
                $foundUsers = \App\Models\User::where(function ($q) {
                    $q->where('name', 'like', '%' . $this->searchMember . '%')
                        ->orWhere('email', 'like', '%' . $this->searchMember . '%');
                })
                    ->whereDoesntHave('roles', fn($q) => $q->where('id', $this->role->id))
                    ->take(5)
                    ->get();
            }
        }

        return view('livewire.admin.access.role-editor', [
            'groupedPermissions' => $groupedPermissions,
            'permissionMap' => $this->getPermissionMap(),
            'permissionGroupMap' => $this->getPermissionGroupMap(),
            'staticRoles' => $this->getStaticRoles(),
            'members' => $members,
            'foundUsers' => $foundUsers,
        ])->layout('layouts.app');
    }

    protected function getPermissionMap()
    {
        return config('access.permission_labels', []);
    }

    protected function getPermissionGroupMap(): array
    {
        return config('access.permission_group_labels', []);
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

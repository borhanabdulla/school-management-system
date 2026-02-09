<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Domains\HR\Teacher\Models\Teacher;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Permissions
        $permissions = [
            'close.year',

            'students.view',
            'students.create',
            'students.edit',
            'students.delete',
            'students.promote',

            'curriculum.manage',
            'classes.manage',
            'timetable.manage',

            'marks.view',
            'marks.edit',
            'marks.override',

            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.delete',

            'attendance.manage',
            'attendance.view',

            'leaves.approve',
            'leave.request',

            'payroll.manage',

            'finance.apply_discount',
            'finance.record_payment',
            'finance.cancel_payment',

            'roles.manage',
            'settings.edit',
            'logs.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Create Roles
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $accountantRole = Role::firstOrCreate(['name' => 'accountant']);

        // 3. Assign Permissions to Roles
        $teacherRole->syncPermissions([
            'marks.view',
            'marks.edit',
            'attendance.view',
            'leave.request', // Assuming this exists or is handled by logic
        ]);

        $accountantRole->syncPermissions([
            'finance.apply_discount',
        ]);

        $adminRole->syncPermissions(Permission::all());

        // 4. Assign Roles to Users
        // Assign 'teacher' role to all users who are teachers
        $teachers = Teacher::with('staff.user')->get();
        foreach ($teachers as $teacher) {
            if ($teacher->staff && $teacher->staff->user) {
                $teacher->staff->user->assignRole($teacherRole);
            }
        }

        $this->command->info('Roles and permissions seeded successfully.');
    }
}

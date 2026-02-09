<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Shared\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class QuickAdminSeeder extends Seeder
{
    public function run()
    {
        // Ensure permission exists
        Permission::firstOrCreate(['name' => 'finance.apply_discount']);

        // Create Admin User
        $user = User::firstOrCreate(
            ['email' => 'admin@school.com'],
            [
                'password' => bcrypt('password'), // simple password for dev
                'username' => 'admin',
            ]
        );

        // Assign Role/Permission
        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->givePermissionTo(Permission::all());
        $user->assignRole($role);

        $this->command->info('User Created:');
        $this->command->info('Email: admin@school.com');
        $this->command->info('Password: password');
    }
}

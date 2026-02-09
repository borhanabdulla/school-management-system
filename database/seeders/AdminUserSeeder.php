<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Shared\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure Super Admin role exists
        if (!Role::where('name', 'Super Admin')->exists()) {
            Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@school.com'],
            [
                'username' => 'admin',
                'password' => Hash::make('password'),
                'phone' => '0500000000',
                'is_active' => true,
            ]
        );

        $admin->assignRole('Super Admin');

        $this->command->info('✅ Admin User Created:');
        $this->command->info('   Email: admin@school.com');
        $this->command->info('   Password: password');
    }
}

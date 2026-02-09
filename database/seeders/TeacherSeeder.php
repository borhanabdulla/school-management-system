<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\HR\Teacher\Models\Teacher;
use Faker\Factory;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('ar_SA');

        // Create 10 Teachers with unique emails and Arabic names
        for ($i = 1; $i <= 10; $i++) {
            $email = "teacher_{$i}@school.com";

            $user = \App\Domains\Shared\Models\User::firstOrCreate(
                ['email' => $email],
                [
                    'username' => "teacher_{$i}",
                    'phone' => '05' . str_pad($i, 8, '0', STR_PAD_LEFT),
                    'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                    'is_active' => true,
                ]
            );

            $staff = \App\Domains\HR\Staff\Models\Staff::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_number' => 2000 + $i,
                    'first_name' => $faker->firstName,
                    'last_name' => $faker->lastName,
                    'phone' => '05' . str_pad($i, 8, '0', STR_PAD_LEFT),
                    'job_title' => 'معلم',
                    'joining_date' => now(),
                    'status' => 'active',
                ]
            );

            Teacher::firstOrCreate(
                ['staff_id' => $staff->id],
                [
                    'specialization' => $faker->randomElement(['رياضيات', 'علوم', 'لغة عربية', 'لغة إنجليزية', 'تاريخ', 'جغرافيا', 'فيزياء', 'كيمياء']),
                ]
            );
        }
    }
}

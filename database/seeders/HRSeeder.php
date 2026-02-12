<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Domains\HR\WorkShift\Models\WorkShift;
use App\Domains\HR\Leave\Models\LeaveType;
use App\Domains\HR\Leave\Models\LeaveRequest;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Staff\Models\StaffAttendance;
use App\Domains\Shared\Models\User;
use App\Domains\HR\Staff\Enums\StaffStatus;
use App\Domains\HR\Leave\Enums\LeaveRequestStatus;
use App\Domains\HR\Staff\Enums\StaffAttendanceStatus;

class HRSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('⏳ Seeding Comprehensive HR Data...');

        DB::transaction(function () {
            // 1. Seed Work Shifts
            $morningShift = $this->seedWorkShifts();

            // 2. Seed Leave Types
            $this->seedLeaveTypes();

            // 3. Seed Specific Staff (Admin & Driver)
            $this->seedStaff($morningShift);
        });

        $this->command->info('✅ HR Data Seeded Successfully!');
    }

    private function seedWorkShifts(): WorkShift
    {
        $shift = WorkShift::firstOrCreate(
            ['name' => 'الدوام الصباحي (Morning Shift)'],
            [
                'season' => 'all',
                'start_time' => '07:30',
                'end_time' => '14:30',
                'grace_period_minutes' => 15,
                'working_days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
                'works_on_holidays' => false,
                'is_active' => true,
            ]
        );

        $this->command->info('   - Work Shift Created: ' . $shift->name);
        return $shift;
    }

    private function seedLeaveTypes(): void
    {
        $types = [
            [
                'name' => 'إجازة سنوية (Annual Leave)',
                'days_per_year' => 30,
                'excludes_holidays' => true,
                'requires_proof' => false,
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'إجازة مرضية (Sick Leave)',
                'days_per_year' => 15,
                'excludes_holidays' => false,
                'requires_proof' => true,
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'إجازة اضطرارية (Emergency Leave)',
                'days_per_year' => 5,
                'excludes_holidays' => false,
                'requires_proof' => false,
                'is_paid' => true,
                'is_active' => true,
            ],
        ];

        foreach ($types as $data) {
            LeaveType::firstOrCreate(['name' => $data['name']], $data);
        }
        $this->command->info('   - Leave Types Created.');
    }

    private function seedStaff(WorkShift $shift): void
    {
        // 1. Admin Officer (Sarah Admin)
        $adminUser = User::firstOrCreate(
            ['email' => 'sarah.admin@school.com'],
            [
                'username' => 'Sarah Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        // Assign Role (HR / Admin)
        $adminUser->assignRole('hr_manager');

        $adminStaff = Staff::firstOrCreate(
            ['user_id' => $adminUser->id],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Admin',
                'job_title' => 'Administrative Officer', // Plain string
                'status' => StaffStatus::Active,
                'work_shift_id' => $shift->id,
                'joining_date' => now()->subYears(2),
                'phone' => '0500000098',
                'employee_number' => 'ADM001',
            ]
        );

        // 2. Driver (Mohammed Driver)
        $driverUser = User::firstOrCreate(
            ['email' => 'mohammed.driver@school.com'],
            [
                'username' => 'Mohammed Driver',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        // Ensure 'staff' role exists for general employees
        $staffRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $driverUser->assignRole($staffRole);

        $driverStaff = Staff::firstOrCreate(
            ['user_id' => $driverUser->id],
            [
                'first_name' => 'Mohammed',
                'last_name' => 'Driver',
                'job_title' => 'Driver',
                'status' => StaffStatus::Active,
                'work_shift_id' => $shift->id,
                'joining_date' => now()->subYears(1),
                'phone' => '0500000099',
                'employee_number' => 'DRV001',
            ]
        );

        $this->command->info('   - Staff Created: Admin & Driver.');

        // Seed Attendance & Leaves for them
        $this->seedAttendance($adminStaff, $driverStaff, $shift);
        $this->seedRequests($driverStaff);
    }

    private function seedAttendance(Staff $admin, Staff $driver, WorkShift $shift): void
    {
        // Generate last 5 working days
        $days = 5;
        $date = now();

        while ($days > 0) {
            if ($date->isWeekend()) {
                $date->subDay();
                continue;
            }

            // Admin: Perfect Attendance
            StaffAttendance::firstOrCreate(
                ['staff_id' => $admin->id, 'date' => $date->toDateString()],
                [
                    'check_in' => '07:25:00',
                    'check_out' => '14:35:00',
                    'status' => StaffAttendanceStatus::Present,
                    'source' => 'system',
                    'recorded_by' => User::first()->id ?? 1,
                ]
            );

            // Driver: 1 Late, 1 Absent, rest Present
            if ($days == 2) { // Late
                StaffAttendance::firstOrCreate(
                    ['staff_id' => $driver->id, 'date' => $date->toDateString()],
                    [
                        'check_in' => '08:00:00', // 30 mins late
                        'check_out' => '14:30:00',
                        'status' => StaffAttendanceStatus::Late,
                        'delay_minutes' => 30,
                        'source' => 'system',
                        'recorded_by' => User::first()->id ?? 1,
                    ]
                );
            } elseif ($days == 4) { // Absent
                StaffAttendance::firstOrCreate(
                    ['staff_id' => $driver->id, 'date' => $date->toDateString()],
                    [
                        'status' => StaffAttendanceStatus::Absent,
                        'source' => 'system',
                        'recorded_by' => User::first()->id ?? 1,
                    ]
                );
            } else { // Present
                StaffAttendance::firstOrCreate(
                    ['staff_id' => $driver->id, 'date' => $date->toDateString()],
                    [
                        'check_in' => '07:15:00',
                        'check_out' => '14:45:00',
                        'status' => StaffAttendanceStatus::Present,
                        'source' => 'system',
                        'recorded_by' => User::first()->id ?? 1,
                    ]
                );
            }

            $date->subDay();
            $days--;
        }
        $this->command->info('   - Attendance Records Generated.');
    }

    private function seedRequests(Staff $driver): void
    {
        $sickLeave = LeaveType::where('name', 'like', '%Sick%')->first();

        if ($sickLeave) {
            LeaveRequest::create([
                'staff_id' => $driver->id,
                'leave_type_id' => $sickLeave->id,
                'start_date' => now()->addDays(5),
                'end_date' => now()->addDays(6),
                'days_count' => 2,
                'reason' => 'Scheduled medical appointment',
                'status' => LeaveRequestStatus::Pending,
            ]);
            $this->command->info('   - Leave Request Created: Pending Sick Leave for Driver.');
        }
    }
}

<?php

namespace App\Domains\HR\Teacher\Actions;

use App\Domains\Shared\Models\User;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\HR\Teacher\Data\TeacherOnboardingData;
use App\Domains\HR\Teacher\Events\TeacherCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CreateTeacherAction
{
    public function execute(TeacherOnboardingData $data): Teacher
    {
        // 1. Validate Business Rules
        if ($data->max_weekly_classes > 40) {
            throw \App\Domains\HR\Teacher\Exceptions\TeacherException::maxClassesExceeded($data->max_weekly_classes, 40);
        }

        if (\Carbon\Carbon::parse($data->hire_date)->isFuture()) {
            throw \App\Domains\HR\Teacher\Exceptions\TeacherException::invalidHireDate($data->hire_date);
        }

        if (User::where('email', $data->email)->exists()) {
            throw \App\Domains\HR\Teacher\Exceptions\TeacherException::duplicateEmail($data->email);
        }

        return DB::transaction(function () use ($data) {
            // Generate username from email prefix
            // Generate username from email prefix
            $baseUsername = explode('@', $data->email)[0];
            $username = $baseUsername;
            $counter = 1;

            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            // Handle Photo Upload
            $profilePhotoPath = null;
            if ($data->photo) {
                $profilePhotoPath = $data->photo->store('profile-photos', 'public');
            }

            // المستوى 1: إنشاء User
            $user = User::create([
                'username' => $username,
                'email' => $data->email,
                'password' => Hash::make($data->password),
                'profile_photo_path' => $profilePhotoPath,
            ]);

            // إسناد صلاحية "معلم"
            // $user->assignRole('teacher');

            // المستوى 2: إنشاء Staff
            $staffNumber = $this->generateStaffNumber();

            $staff = Staff::create([
                'user_id' => $user->id,
                'first_name' => $data->first_name,
                'last_name' => $data->last_name,
                'employee_number' => $staffNumber,
                'phone' => !empty($data->phone) ? $data->phone : $staffNumber, // Phone is unique, use staff number as fallback if null or empty
                'joining_date' => $data->hire_date,
            ]);

            // المستوى 3: إنشاء Teacher
            $teacher = Teacher::create([
                'staff_id' => $staff->id,
                'specialization' => $data->specialization,
                'max_weekly_classes' => $data->max_weekly_classes,
            ]);

            // Fire Event (Business Workflow)
            event(new TeacherCreated($teacher));

            return $teacher->load('staff.user');
        });
    }

    /**
     * توليد رقم وظيفي فريد
     */
    protected function generateStaffNumber(): string
    {
        $year = date('Y');
        $lastStaff = Staff::latest('id')->first();
        $sequence = $lastStaff ? ($lastStaff->id + 1) : 1;

        return 'STF-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}

<?php

namespace App\Domains\HR\Staff\Actions;

use App\Data\Staff\StaffOnboardingData;
use App\Domains\HR\Enums\StaffRole;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\Shared\Models\User;
use App\Notifications\StaffWelcomeNotification;
use App\Domains\HR\Shared\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Action موحد لإضافة موظف جديد
 * يغطي جميع أنواع الموظفين (معلم، إداري، محاسب، سائق، حارس)
 */
class CreateStaffAction
{
    public function __construct(
        private AuditLogService $auditLog
    ) {
    }

    /**
     * تنفيذ عملية إضافة الموظف
     * 
     * @param StaffOnboardingData $data
     * @return Staff الموظف المُنشأ مع العلاقات
     */
    public function execute(StaffOnboardingData $data): Staff
    {
        $user = null;
        $password = $data->password ?? $this->generateSecurePassword();

        $staff = DB::transaction(function () use ($data, &$user, $password) {
            // 1. إنشاء حساب المستخدم (اختياري)
            if ($data->create_account) {
                $user = $this->createUser($data, $password);
            }

            // 2. إنشاء سجل الموظف
            $staff = $this->createStaff($data, $user);

            // 3. إنشاء سجل المعلم (إذا كان الدور معلم)
            if ($data->role->requiresTeacherRecord()) {
                $this->createTeacher($staff, $data);
            }

            // 4. تعيين الصلاحيات (إذا وُجد حساب)
            if ($user) {
                $user->assignRole($data->role->value);
            }

            // 5. تسجيل في سجل التدقيق
            $this->auditLog->logCreated($staff);

            Log::info('Staff created successfully', [
                'staff_id' => $staff->id,
                'user_id' => $user?->id,
                'role' => $data->role->value,
                'created_by' => auth()->id()
            ]);

            return $staff;
        });

        // 6. إرسال إشعار ترحيبي (خارج الـ transaction لتجنب فشل التسجيل)
        if ($user) {
            try {
                $user->notify(new StaffWelcomeNotification($user->email, $password));
            } catch (\Exception $e) {
                Log::warning('Failed to send welcome notification', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $staff->load(['user', 'teacher', 'workShift']);
    }

    /**
     * إنشاء حساب المستخدم
     */
    protected function createUser(StaffOnboardingData $data, string $password): User
    {
        $username = $this->generateUsername($data->email);

        return User::create([
            'name' => "{$data->first_name} {$data->last_name}",
            'username' => $username,
            'email' => $data->email,
            'phone' => $data->phone,
            'password' => Hash::make($password),
        ]);
    }

    /**
     * إنشاء سجل الموظف
     * ملاحظة: البريد الإلكتروني يُحفظ في جدول users فقط
     */
    protected function createStaff(StaffOnboardingData $data, ?User $user): Staff
    {
        return Staff::create([
            'user_id' => $user?->id,
            'employee_number' => $this->generateEmployeeNumber(),
            'first_name' => $data->first_name,
            'last_name' => $data->last_name,
            'phone' => $data->phone,
            'joining_date' => $data->joining_date,
            'work_shift_id' => $data->work_shift_id,
            'employment_type' => $data->employment_type,
            'job_title' => $data->job_title,
        ]);
    }

    /**
     * إنشاء سجل المعلم
     */
    protected function createTeacher(Staff $staff, StaffOnboardingData $data): Teacher
    {
        return Teacher::create([
            'staff_id' => $staff->id,
            'specialization' => $data->specialization,
            'max_weekly_classes' => $data->max_weekly_classes ?? 24,
        ]);
    }

    /**
     * توليد اسم مستخدم فريد من البريد الإلكتروني
     */
    protected function generateUsername(string $email): string
    {
        $username = explode('@', $email)[0];
        $username = Str::slug($username, '_');

        // التأكد من عدم التكرار
        $originalUsername = $username;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . '_' . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * توليد رقم وظيفي فريد
     * Format: STF-YYYY-XXXX
     */
    protected function generateEmployeeNumber(): string
    {
        $year = date('Y');
        $lastStaff = Staff::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastStaff ? ($lastStaff->id + 1) : 1;

        return 'STF-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * توليد كلمة مرور آمنة عشوائية
     */
    protected function generateSecurePassword(): string
    {
        // Format: School@XXXX (4 أرقام عشوائية)
        return 'School@' . rand(1000, 9999);
    }
}

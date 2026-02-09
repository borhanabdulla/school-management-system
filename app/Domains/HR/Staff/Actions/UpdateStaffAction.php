<?php

namespace App\Domains\HR\Staff\Actions;

use App\Data\Staff\StaffOnboardingData;
use App\Domains\HR\Enums\StaffRole;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\HR\Shared\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Action لتحديث بيانات موظف
 */
class UpdateStaffAction
{
    public function __construct(
        private AuditLogService $auditLog
    ) {
    }

    /**
     * تحديث بيانات الموظف
     */
    public function execute(Staff $staff, array $data): Staff
    {
        $oldData = $staff->toArray();

        return DB::transaction(function () use ($staff, $data, $oldData) {
            // 1. تحديث بيانات الموظف الأساسية
            $staff->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'] ?? $staff->phone,
                'joining_date' => $data['joining_date'],
                'work_shift_id' => $data['work_shift_id'],
                'employment_type' => $data['employment_type'],
                'job_title' => $data['job_title'],
                'status' => $data['status'] ?? $staff->status,
            ]);

            // 2. تحديث بيانات المستخدم (إذا وجد)
            if ($staff->user) {
                $staff->user->update([
                    'name' => "{$data['first_name']} {$data['last_name']}",
                ]);

                // تحديث الصلاحية إذا تغير الدور
                if (isset($data['role'])) {
                    $newRole = StaffRole::from($data['role']);
                    $staff->user->syncRoles([$newRole->value]);
                }
            }

            // 3. التعامل مع سجل المعلم
            $newRole = isset($data['role']) ? StaffRole::from($data['role']) : null;

            if ($newRole?->requiresTeacherRecord()) {
                // إنشاء سجل معلم إذا لم يكن موجوداً
                if (!$staff->teacher) {
                    Teacher::create([
                        'staff_id' => $staff->id,
                        'specialization' => $data['specialization'] ?? null,
                        'max_weekly_classes' => $data['max_weekly_classes'] ?? 24,
                    ]);
                } else {
                    // تحديث سجل المعلم
                    $staff->teacher->update([
                        'specialization' => $data['specialization'] ?? $staff->teacher->specialization,
                        'max_weekly_classes' => $data['max_weekly_classes'] ?? $staff->teacher->max_weekly_classes,
                    ]);
                }
            }

            // 4. Audit Log
            $this->auditLog->logUpdated($staff, $oldData, $staff->fresh()->toArray());

            Log::info('Staff updated successfully', [
                'staff_id' => $staff->id,
                'updated_by' => auth()->id()
            ]);

            return $staff->fresh(['user', 'teacher', 'workShift']);
        });
    }
}

<?php

namespace App\Domains\HR\Staff\Actions;

use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Staff\Enums\StaffStatus;
use App\Domains\HR\Shared\Services\AuditLogService;
use App\Domains\HR\Teacher\Actions\DeleteTeacherAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Action لحذف موظف
 * يتضمن فحوصات أمان لتجنب حذف موظفين مرتبطين ببيانات مهمة
 */
class DeleteStaffAction
{
    public function __construct(
        private AuditLogService $auditLog,
        private DeleteTeacherAction $deleteTeacherAction
    ) {
    }

    /**
     * التحقق من إمكانية الحذف
     * 
     * @return array ['can_delete' => bool, 'reasons' => array]
     */
    public function canDelete(Staff $staff): array
    {
        $reasons = [];

        // 1. فحص سجلات الحضور
        $attendanceCount = $staff->attendances()->count();
        if ($attendanceCount > 0) {
            $reasons[] = "لديه {$attendanceCount} سجل حضور";
        }

        // 2. إذا كان معلماً، فحص الجدول الدراسي
        if ($staff->teacher) {
            $teacherCheck = $this->deleteTeacherAction->canDelete($staff->teacher);
            if (!$teacherCheck['can_delete']) {
                $reasons = array_merge($reasons, $teacherCheck['reasons']);
            }
        }

        return [
            'can_delete' => empty($reasons),
            'reasons' => $reasons,
        ];
    }

    /**
     * حذف الموظف
     * 
     * @param bool $force - حذف بالقوة حتى لو كانت هناك سجلات مرتبطة
     */
    public function execute(Staff $staff, bool $force = false): bool
    {
        $check = $this->canDelete($staff);

        if (!$check['can_delete'] && !$force) {
            throw new \App\Domains\HR\Staff\Exceptions\StaffNotDeletableException($check['reasons']);
        }

        return DB::transaction(function () use ($staff) {
            $staffData = $staff->toArray();

            // 1. حذف سجل المعلم (إذا وجد)
            if ($staff->teacher) {
                $this->deleteTeacherAction->execute($staff->teacher);
            }

            // 2. حذف سجلات الحضور
            $staff->attendances()->delete();

            // 3. Audit Log قبل الحذف
            $this->auditLog->logDeleted($staff);

            // 4. حذف الموظف
            $staffId = $staff->id;
            $userId = $staff->user_id;
            $staff->delete();

            // 5. حذف حساب المستخدم (اختياري)
            // ملاحظة: نتركه للإدارة اليدوية لأن الحساب قد يكون مطلوباً
            // if ($userId) {
            //     \App\Models\User::find($userId)?->delete();
            // }

            Log::info('Staff deleted successfully', [
                'staff_id' => $staffId,
                'deleted_by' => auth()->id()
            ]);

            return true;
        });
    }

    /**
     * تعطيل الموظف بدلاً من الحذف (الخيار الآمن)
     */
    public function deactivate(Staff $staff): Staff
    {
        $oldStatus = $staff->status;

        $staff->update(['status' => StaffStatus::Terminated]);

        $this->auditLog->logUpdated($staff, ['status' => $oldStatus]);

        Log::info('Staff deactivated', [
            'staff_id' => $staff->id,
            'by' => auth()->id()
        ]);

        return $staff;
    }
}

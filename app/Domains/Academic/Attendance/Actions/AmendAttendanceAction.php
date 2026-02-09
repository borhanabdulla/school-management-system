<?php

declare(strict_types=1);

namespace App\Domains\Academic\Attendance\Actions;

use App\Domains\Academic\Attendance\Models\Attendance;
use App\Infrastructure\Exceptions\InvalidOperationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * AmendAttendanceAction - إجراء تعديل الحضور
 *
 * يسمح للمدير بتعديل سجل حضور طالب بعد إغلاق السنة الدراسية
 * مع تسجيل السبب وبيانات Audit
 *
 * ⚠️ يتطلب صلاحية: amend.attendance
 * ⚠️ السبب إلزامي للتعديل
 */
class AmendAttendanceAction
{
    /**
     * تعديل سجل حضور
     *
     * @param Attendance $attendance سجل الحضور المراد تعديله
     * @param string $status الحالة الجديدة (present, absent, late, excuse)
     * @param string $reason سبب التعديل (إلزامي)
     * @param string|null $notes ملاحظات إضافية
     * @return Attendance السجل المعدل
     *
     * @throws InvalidOperationException إذا لم يكن لديه صلاحية أو لم يقدم سبب
     */
    public function execute(
        Attendance $attendance,
        string $status,
        string $reason,
        ?string $notes = null
    ): Attendance {
        // التحقق من صلاحية المستخدم
        $this->authorize();

        // التحقق من وجود سبب التعديل
        $this->validateReason($reason);

        // التحقق من صحة الحالة
        $this->validateStatus($status);

        return DB::transaction(function () use ($attendance, $status, $reason, $notes) {
            // الاحتفاظ بالبيانات القديمة للتAudit
            $oldStatus = $attendance->status;

            // تنفيذ التعديل
            $attendance->update([
                'status' => $status,
                'amended_by' => Auth::id(),
                'amended_at' => now(),
                'amendment_reason' => $reason,
                'remarks' => $notes,
            ]);

            // إعادة تحميل العلاقة
            $attendance->refresh();

            // تسجيل Audit (TODO: تفعيل AuditLog Table لاحقاً)
            $this->logAmendment($attendance, $oldStatus, $status, $reason);

            return $attendance;
        });
    }

    /**
     * التحقق من صلاحية المستخدم
     */
    protected function authorize(): void
    {
        // TODO: تفعيل الصلاحيات عند توفر نظام Permissions
        // if (!Auth::user()->can('amend.attendance')) {
        //     throw new InvalidOperationException('ليس لديك صلاحية تعديل الحضور');
        // }

        // حالياً نسمح فقط للمستخدمين المسجلين
        if (!Auth::check()) {
            throw new InvalidOperationException('يجب تسجيل الدخول لتعديل الحضور');
        }
    }

    /**
     * التحقق من سبب التعديل
     */
    protected function validateReason(string $reason): void
    {
        if (empty(trim($reason))) {
            throw new InvalidOperationException('سبب التعديل مطلوب ولا يمكن تركه فارغاً');
        }

        if (strlen($reason) < 10) {
            throw new InvalidOperationException('سبب التعديل يجب أن يكون 10 أحرف على الأقل');
        }
    }

    /**
     * التحقق من صحة حالة الحضور
     */
    protected function validateStatus(string $status): void
    {
        $validStatuses = ['present', 'absent', 'late', 'excuse', 'early_leave'];

        if (!in_array(strtolower($status), $validStatuses)) {
            throw new InvalidOperationException('حالة الحضور غير صالحة. القيم المسموحة: ' . implode(', ', $validStatuses));
        }
    }

    /**
     * تسجيل عملية التعديل
     */
    protected function logAmendment(
        Attendance $attendance,
        string $oldStatus,
        string $newStatus,
        string $reason
    ): void {
        // TODO: تفعيل AuditLog Table لاحقاً
        logger()->info('Attendance amended', [
            'attendance_id' => $attendance->id,
            'student_id' => $attendance->student_id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'amended_by' => Auth::id(),
            'reason' => $reason,
        ]);
    }
}

<?php

namespace App\Domains\Academic\Attendance\Services;

use App\Domains\Academic\Attendance\Data\AttendanceSettingsData;
use App\Domains\Academic\Attendance\Models\Attendance;
use App\Domains\Academic\Attendance\Models\AttendanceSetting;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use Exception;

class AttendanceSettingsService
{
    /**
     * الحصول على إعدادات السنة الدراسية، أو إنشاء افتراضية
     */
    public function getSettings(int $academicYearId): AttendanceSetting
    {
        return AttendanceSetting::firstOrCreate(
            ['academic_year_id' => $academicYearId],
            [
                'mode' => 'checkpoints',
                'responsible_role' => 'subject_teacher',
                'late_tolerance' => 15
            ]
        );
    }

    /**
     * تحديث الإعدادات مع تطبيق قواعد العمل
     */
    public function updateSettings(AttendanceSetting $settings, AttendanceSettingsData $data): AttendanceSetting
    {
        // Rule-01: التحقق من وجود بيانات سابقة عند محاولة تغيير النمط
        // تم تعطيل المنع البرمجي للسماح للمدير بالتغيير مع تحذير في الواجهة
        // if ($settings->mode !== $data->mode->value) {
        //     if ($this->hasExistingAttendanceRecords($settings->academic_year_id)) {
        //         throw new Exception('لا يمكن تغيير نمط الحضور لوجود سجلات حضور مسجلة بالفعل لهذه السنة. هذا قد يسبب تضارباً في التقارير.');
        //     }
        // }

        $settings->update([
            'mode' => $data->mode->value,
            'responsible_role' => $data->responsible_role->value,
            'late_tolerance' => $data->late_tolerance,
        ]);

        return $settings;
    }

    /**
     * التحقق من وجود سجلات حضور لهذه السنة
     */
    public function hasExistingAttendanceRecords(int $academicYearId): bool
    {
        // نتحقق من وجود أي سجل حضور مرتبط بحصة في هذه السنة الدراسية
        // ملاحظة: العلاقة بين Attendance و AcademicYear غير مباشرة عبر ClassSection أو TimeSlot->Template
        // الأسرع هو التحقق عبر ClassSection

        return Attendance::whereHas('classSection', function ($q) use ($academicYearId) {
            $q->where('academic_year_id', $academicYearId);
        })->exists(); // الاستعلام  هنا يعمل على جلب ر الحضور    ومسنخدم  داله wharehas من اجل البحث بالسنة الدراسية رحيث تعمل هذه الداله على البحث بالسنة الدراسية     
    }
}

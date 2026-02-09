<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Actions\Admin;

use App\Domains\Academic\Grading\Data\MonthlySettingsData;
use App\Domains\Academic\Grading\Models\GradebookSettings;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grading\Exceptions\GradingException;
use Illuminate\Support\Facades\Auth;

/**
 * SaveGradebookSettingsAction - حفظ إعدادات سجل الدرجات الشهري
 * 
 * @responsibility حفظ إعدادات MonthlyGradebook لسنة دراسية محددة
 */
class SaveGradebookSettingsAction
{
    /**
     * تنفيذ Action لحفظ إعدادات سجل الدرجات
     * 
     * @param int $academicYearId معرف السنة الدراسية (صريح)
     * @param MonthlySettingsData $data بيانات الإعدادات
     * @return void
     * @throws GradingException
     */
    public function execute(int $academicYearId, MonthlySettingsData $data): void
    {
        $this->authorize();
        $this->validateYearExists($academicYearId);

        // جلب أو إنشاء إعدادات للسنة المحددة
        $settings = GradebookSettings::firstOrCreate(
            ['academic_year_id' => $academicYearId],
            [
                'monthly_categories' => [],
                'attendance_deduct_after' => 0,
                'attendance_deduct_per_absence' => 0,
                'attendance_max_score' => 0,
                'allow_custom_categories' => false,
            ]
        );

        // تحديث الإعدادات
        $settings->update([
            'monthly_categories' => GradebookSettings::normalizeMonthlyCategories($data->categories),
            'attendance_deduct_after' => $data->attendanceDeductAfter,
            'attendance_deduct_per_absence' => $data->attendanceDeductPerAbsence,
            'attendance_max_score' => $data->attendanceMaxScore,
            'allow_custom_categories' => $data->allowCustomCategories,
        ]);
    }

    /**
     * التحقق من الصلاحيات
     */
    protected function authorize(): void
    {
        if (!Auth::check()) {
            throw new GradingException('يجب تسجيل الدخول أولاً.');
        }

        if (!Auth::user()->can('grading.manage_settings')) {
            throw new GradingException('ليس لديك صلاحية إدارة إعدادات سجل الدرجات.');
        }
    }

    /**
     * التحقق من وجود السنة الدراسية
     * 
     * Guard: يمنع الحفظ لسنة غير موجودة
     * 
     * @param int $academicYearId
     * @throws GradingException
     */
    protected function validateYearExists(int $academicYearId): void
    {
        if (!AcademicYear::where('id', $academicYearId)->exists()) {
            throw new GradingException("السنة الدراسية #{$academicYearId} غير موجودة.");
        }
    }
}

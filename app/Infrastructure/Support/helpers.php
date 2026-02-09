<?php

declare(strict_types=1);

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * School Dashboard - Global Helpers
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * هذا الملف يحتوي على الدوال المساعدة (Helper Functions) المتاحة عالمياً
 * في كل مكان في التطبيق: Controllers, Livewire, Blade, etc.
 * 
 * @author School Dashboard Team
 * @version 2.0
 */

use App\Infrastructure\Context\AcademicContextService;

if (!function_exists('school')) {
    /**
     * الوصول السريع لـ AcademicContextService
     * 
     * هذه الدالة هي البوابة الرئيسية للوصول إلى:
     * - السنة الدراسية النشطة
     * - الفصل الدراسي الحالي
     * - إعدادات النظام
     * 
     * @return AcademicContextService
     * 
     * @example
     * // الحصول على السنة النشطة
     * $year = school()->activeYear();
     * 
     * // الحصول على معرف السنة للاستعلامات
     * Student::where('academic_year_id', school()->activeYearId())->get();
     * 
     * // الحصول على الفصل النشط
     * $term = school()->activeTerm();
     * 
     * // الحصول على إعداد معين
     * $passMark = school()->setting('grading.pass_mark', 50);
     * 
     * // التحقق من وجود سنة نشطة
     * if (school()->hasActiveYear()) {
     *     // ...
     * }
     */
    function school(): AcademicContextService
    {
        return AcademicContextService::getInstance();
    }
}

if (!function_exists('active_year_id')) {
    /**
     * اختصار للحصول على معرف السنة النشطة
     * 
     * مفيد جداً في الاستعلامات
     * 
     * @return int|null
     * 
     * @example
     * Student::where('academic_year_id', active_year_id())->get();
     */
    function active_year_id(): ?int
    {
        return school()->activeYearId();
    }
}

if (!function_exists('active_term_id')) {
    /**
     * اختصار للحصول على معرف الفصل النشط
     * 
     * @return int|null
     * 
     * @example
     * Attendance::where('term_id', active_term_id())->get();
     */
    function active_term_id(): ?int
    {
        return school()->activeTermId();
    }
}

if (!function_exists('setting')) {
    /**
     * اختصار للحصول على إعداد من النظام
     * 
     * @param string $key مفتاح الإعداد
     * @param mixed $default القيمة الافتراضية
     * @return mixed
     * 
     * @example
     * $schoolName = setting('school.name', 'مدرستنا');
     * $passMark = setting('grading.pass_mark', 50);
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return school()->setting($key, $default);
    }
}

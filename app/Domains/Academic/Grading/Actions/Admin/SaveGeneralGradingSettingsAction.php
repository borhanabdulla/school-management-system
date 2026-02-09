<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Actions\Admin;

use App\Domains\Academic\Grading\Data\GeneralSettingsData;
use App\Domains\Academic\Grading\Models\SystemSetting;
use App\Domains\Academic\Grading\Exceptions\GradingException;
use Illuminate\Support\Facades\Auth;

/**
 * SaveGeneralGradingSettingsAction - حفظ الإعدادات العامة للدرجات
 * 
 * @responsibility حفظ defaultPassScore، graceMarksLimit، termWeights
 * @guard يتحقق من أن مجموع termWeights = 100
 */
class SaveGeneralGradingSettingsAction
{
    /**
     * تنفيذ Action لحفظ الإعدادات العامة
     * 
     * @param GeneralSettingsData $data
     * @return void
     * @throws GradingException
     */
    public function execute(GeneralSettingsData $data): void
    {
        $this->authorize();
        $this->validate($data);

        // حفظ كل إعداد في system_settings
        SystemSetting::set('grading.default_pass_score', $data->defaultPassScore);
        SystemSetting::set('grading.grace_marks_limit', $data->graceMarksLimit);
        SystemSetting::set('grading.term_weights', $data->termWeights);

        // إبطال cache (إذا كان موجوداً)
        app(\App\Infrastructure\Context\AcademicContextService::class)->invalidateSettings();
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
            throw new GradingException('ليس لديك صلاحية إدارة إعدادات الدرجات. الصلاحية المطلوبة: grading.manage_settings');
        }
    }

    /**
     * التحقق من صحة البيانات
     * 
     * @param GeneralSettingsData $data
     * @throws GradingException
     */
    protected function validate(GeneralSettingsData $data): void
    {
        // التحقق من defaultPassScore
        if ($data->defaultPassScore < 0 || $data->defaultPassScore > 100) {
            throw new GradingException('درجة النجاح الافتراضية يجب أن تكون بين 0 و 100');
        }

        // التحقق من graceMarksLimit
        if ($data->graceMarksLimit < 0 || $data->graceMarksLimit > 10) {
            throw new GradingException('حد درجات الرأفة يجب أن يكون بين 0 و 10');
        }

        // التحقق من أن مجموع أوزان الترمات = 100
        $totalWeight = array_sum($data->termWeights);
        if (abs($totalWeight - 100) > 0.01) { // float tolerance
            throw new GradingException(
                "مجموع أوزان الفصول الدراسية يجب أن يساوي 100%. المجموع الحالي: {$totalWeight}%"
            );
        }

        // التحقق من أن كل وزن موجب
        foreach ($data->termWeights as $termId => $weight) {
            if ($weight < 0 || $weight > 100) {
                throw new GradingException("وزن الفصل #{$termId} يجب أن يكون بين 0 و 100");
            }
        }
    }
}

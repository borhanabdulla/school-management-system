<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Actions\Admin;

use App\Domains\Academic\Grading\Data\GradeScaleData;
use App\Domains\Academic\Grading\Models\SystemSetting;
use App\Domains\Academic\Grading\Services\GradeScaleValidator;
use App\Domains\Academic\Grading\Services\GradingLookupService;
use App\Domains\Academic\Grading\Exceptions\GradingException;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Services\AcademicWriteGuard;
use Illuminate\Support\Facades\Auth;

/**
 * SaveGradeScaleAction - حفظ سلم الدرجات
 * 
 * @responsibility حفظ سلم تقديرات الدرجات مع التحقق من عدم التداخل
 */
class SaveGradeScaleAction
{
    public function __construct(
        private readonly GradeScaleValidator $validator,
        private readonly GradingLookupService $lookupService
    ) {
    }

    /**
     * تنفيذ Action لحفظ سلم الدرجات
     * 
     * @param GradeScaleData $data
     * @return void
     * @throws GradingException
     */
    public function execute(GradeScaleData $data): void
    {
        $this->authorize();
        $this->assertWritable();

        // التحقق من صحة السلم (عدم التداخل)
        $this->validator->validate($data->scale);

        // حفظ في system_settings
        SystemSetting::set('grading.scale', $data->scale);

        // إبطال cache سلم الدرجات
        $this->lookupService->invalidateScaleCache();
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
            throw new GradingException('ليس لديك صلاحية إدارة سلم الدرجات.');
        }
    }

    private function assertWritable(): void
    {
        $yearId = school()->activeYearId() ?? AcademicYear::first()?->id;
        if ($yearId) {
            app(AcademicWriteGuard::class)->assertYearNotClosed($yearId);
        }
    }
}

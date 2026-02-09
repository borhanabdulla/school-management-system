<?php

declare(strict_types=1);

namespace App\Domains\Academic\AcademicYear\Actions;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\AcademicYear\Exceptions\InsufficientTermsException;
use App\Domains\Academic\AcademicYear\Events\AcademicYearActivated;
use Illuminate\Support\Facades\DB;
use App\Infrastructure\Exceptions\InvalidOperationException;
// use App\Domains\Academic\AcademicYear\Exceptions\YearNotEditableException;
use App\Domains\Academic\AcademicYear\Exceptions\CurrentYearNotClosableException;

/**
 * ActivateAcademicYearAction - تفعيل السنة الدراسية
 * 
 * تقوم هذه العملية بـ:
 * 1. التحقق من جاهزية السنة للتفعيل
 * 2. إلغاء تفعيل السنة الحالية (إن وجدت)
 * 3. تفعيل السنة الجديدة
 * 
 * @author School Dashboard Team
 * @version 2.1
 */
class ActivateAcademicYearAction
{
    // حقن التبعيات عبر المنشئ (أفضل معمارياً)
    public function __construct(
        protected \App\Domains\Academic\AcademicYear\Validation\AcademicYearClosureValidator $closureValidator
    ) {
    }

    /**
     * تنفيذ التفعيل
     * 
     * @param AcademicYear $year السنة المراد تفعيلها
     * @return void
     * @throws InvalidOperationException إذا كانت السنة غير جاهزة
     */
    public function execute(AcademicYear $year): void
    {
        if ($year->status === AcademicYearStatus::Active) {
            return;
        }

        // ملاحظة: التحقق من الجاهزية يفضل أن يكون قبل الترانزاكشن لتوفير الموارد
        $this->validateInitialState($year);

        DB::transaction(function () use ($year) {

            // 1. قفل السنة الجديدة لضمان عدم معالجتها من طلب آخر
            $year = AcademicYear::where('id', $year->id)->lockForUpdate()->firstOrFail();

            // 2. معالجة السنوات النشطة من خلال المصدر الوحيد للحقيقة (DB)
            $activeYears = AcademicYear::query()
                ->where('status', AcademicYearStatus::Active)
                ->lockForUpdate()
                ->get();

            foreach ($activeYears as $currentYear) {
                $check = $this->closureValidator->validate($currentYear);
                if (!$check['can']) {
                    // رمي استثناء مخصص بدلاً من Exception عامة
                    throw new \App\Domains\Academic\AcademicYear\Exceptions\CurrentYearNotClosableException($currentYear->name, $check['issues']);
                }

                $currentYear->update(['status' => AcademicYearStatus::Closed]);
            }

            // 3. تفعيل السنة الجديدة
            $year->update(['status' => AcademicYearStatus::Active]);

            // ملاحظة: لا يتم تفعيل أي فصل تلقائياً. يتم ذلك يدوياً من شاشة الترمات.

            // 4. إطلاق الحدث
            event(new AcademicYearActivated($year));
        });

        DB::afterCommit(function () {
            school()->invalidateYear();
        });
    }

    protected function validateInitialState(AcademicYear $year): void
    {
        if (!$year->hasMinimumTerms()) {
            throw new InsufficientTermsException();
        }

        if (!$year->isReadyForActivation()) {
            throw InvalidOperationException::cannotActivate(
                'السنة الدراسية',
                'يجب أن تكون في حالة الانتظار'
            );
        }
    }
}

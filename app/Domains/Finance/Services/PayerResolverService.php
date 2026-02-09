<?php

declare(strict_types=1);

namespace App\Domains\Finance\Services;

use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\Guardian;
use App\Domains\Finance\Exceptions\NoFinancialSponsorFoundException;
use App\Domains\Finance\Exceptions\MultipleFinancialSponsorsException;

/**
 * PayerResolverService - خدمة تحديد المسؤول المالي
 * 
 * مسؤولة عن تحديد من هو "الدافع" الذي سيتم تثبيته على الفاتورة.
 * تضمن وجود مسؤول مالي واحد فقط للطالب.
 */
class PayerResolverService
{
    /**
     * تحديد المسؤول المالي للطالب
     * 
     * @throws NoFinancialSponsorFoundException إذا لم يوجد مسؤول مالي
     * @throws MultipleFinancialSponsorsException إذا وجد أكثر من مسؤول مالي (خطأ بيانات)
     */
    public function resolve(Student $student): Guardian
    {
        // نحتاج تحميل العلاقة مع pivot إذا لم تكن محملة، لكن الأضمن الاستعلام المباشر
        // لضمان الدقة وتفادي الـ caching القديم
        $sponsors = $student->guardians()
            ->wherePivot('is_financial_sponsor', true)
            ->get();

        if ($sponsors->isEmpty()) {
            throw new NoFinancialSponsorFoundException($student->id);
        }

        if ($sponsors->count() > 1) {
            throw new MultipleFinancialSponsorsException($student->id, $sponsors->count());
        }

        return $sponsors->first();
    }
}

<?php

declare(strict_types=1);

namespace App\Domains\Finance\Services;

use App\Domains\Finance\Data\DiscountSuggestion;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\Discount;
use Illuminate\Support\Collection;

/**
 * DiscountPolicySuggestionService
 * 
 * خدمة "استشارية" (Read-Only) تقترح الخصومات بناءً على سياسات محددة.
 * تعتمد على Payer Snapshot كمصدر وحيد للحقيقة لتحديد "المسؤول المالي".
 */
class DiscountPolicySuggestionService
{
    // أكواد السياسات
    public const POLICY_PAYER_THIRD_CHILD = 'PAYER_THIRD_CHILD';

    /**
     * الحصول على الاقتراحات لفاتورة معينة
     */
    public function getSuggestions(Invoice $invoice): Collection
    {
        $suggestions = collect();

        // 1. سياسة الطفل الثالث (Third Child Policy)
        if ($suggestion = $this->checkThirdChildPolicy($invoice)) {
            $suggestions->push($suggestion);
        }

        return $suggestions;
    }

    /**
     * منطق سياسة الطفل الثالث
     * - المعيار: عدد الطلاب المميزين الذين تكفل بهم هذا الولي (Payer) في نفس السنة.
     * - الشرط: إذا كان هذا هو الطالب رقم 3 (أو أكثر) لهذا الولي.
     * - المقترح: خصم 100% على الرسوم الدراسية (Tuition).
     */
    private function checkThirdChildPolicy(Invoice $invoice): ?DiscountSuggestion
    {
        // التحقق من وجود payer مثبت
        if (!$invoice->payer_guardian_id) {
            return null;
        }

        // 1. حساب عدد الطلاب الذين تكفل بهم هذا الولي في هذه السنة
        // نعد الـ student_id المميزين من الفواتير المرتبطة بهذا الولي والسنة
        $payerStudentCount = Invoice::where('payer_guardian_id', $invoice->payer_guardian_id)
            ->where('academic_year_id', $invoice->academic_year_id)
            ->where('status', '!=', 'cancelled') // تجاهل الملغاة
            ->distinct('student_id')
            ->count('student_id');

        // إذا لم يصل للعدد المطلوب (مثلاً 3)، لا اقتراح
        if ($payerStudentCount < 3) {
            return null;
        }

        // 2. تحديد البنود القابلة للخصم (Tuition Fees)
        // نفترض أن Tuition هو بند يحمل fee_type معين أو اسم معين. 
        // هنا سنبحث عن بنود مرتبطة بـ Tuition (يمكن تحسينها لاحقاً بـ scope)
        $tuitionItems = $invoice->items->filter(function ($item) {
            return stripos($item->feeType->name, 'Tuition') !== false || stripos($item->feeType->name, 'مدرسة') !== false;
        });

        if ($tuitionItems->isEmpty()) {
            return null;
        }

        // 3. التحقق هل تم تطبيق الخصم مسبقاً على هذه البنود (منع التكرار)
        // إذا أي بند عليه خصم، نعتبره "عولج" ولا نقترح
        foreach ($tuitionItems as $item) {
            if ($item->discountApplications->isNotEmpty()) {
                return null;
            }
        }

        // 4. إيجاد أو اقتراح تعريف الخصم (Discount Definition)
        // نبحث عن خصم معرف مسبقاً باسم "Sibling Discount" أو مشابه، أو نرجع null ليختاره المستخدم
        $discountDef = Discount::where('name', 'LIKE', '%Sibling%')
            ->orWhere('name', 'LIKE', '%Third%')
            ->first();

        // حساب القيمة المقترحة (100% من بنود الدراسة)
        $suggestedAmount = $tuitionItems->sum('amount');

        return new DiscountSuggestion(
            policyCode: self::POLICY_PAYER_THIRD_CHILD,
            description: "هذا الولي المالي يتكفل بـ {$payerStudentCount} طلاب. يستحق خصم الأخوة/الثالث.",
            suggestedDiscountId: $discountDef?->id,
            eligibleItemIds: $tuitionItems->pluck('id')->toArray(),
            suggestedAmount: $suggestedAmount
        );
    }
}

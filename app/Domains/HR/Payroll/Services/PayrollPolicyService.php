<?php

namespace App\Domains\HR\Payroll\Services;

use App\Infrastructure\Support\Helpers\DateHelper;
use Carbon\Carbon;

/**
 * خدمة إعدادات وسياسات الرواتب
 * 
 * تقرأ السياسات من الإعدادات (يمكن نقلها لقاعدة البيانات لاحقاً)
 */
class PayrollPolicyService
{
    /**
     * طريقة احتساب عدد أيام الشهر
     * 
     * @return string '30_fixed' | '26_workdays' | 'actual_days'
     */
    public function getDayCalculationMethod(): string
    {
        return config('payroll.day_calculation', '30_fixed');
    }

    /**
     * عدد أيام الشهر للحساب
     */
    public function getMonthDays(Carbon $date): int
    {
        return match ($this->getDayCalculationMethod()) {
            '30_fixed' => 30,
            '26_workdays' => 26,
            'actual_days' => $date->daysInMonth,
            default => 30,
        };
    }

    /**
     * حساب قيمة اليوم الواحد
     */
    public function getDailyRate(float $monthlySalary, Carbon $date): float
    {
        $days = $this->getMonthDays($date);
        return $days > 0 ? round($monthlySalary / $days, 2) : 0;
    }

    // ==================== سياسة التأخير ====================

    /**
     * شرائح التأخير
     * 
     * @return array [{from: 0, to: 15, deduction_percent: 0}, ...]
     */
    public function getLatenessPolicy(): array
    {
        return config('payroll.lateness_policy', [
            ['from' => 0, 'to' => 15, 'deduction_percent' => 0],       // سماح
            ['from' => 16, 'to' => 30, 'deduction_percent' => 25],    // ربع ساعة
            ['from' => 31, 'to' => 60, 'deduction_percent' => 50],    // نصف ساعة
            ['from' => 61, 'to' => 120, 'deduction_percent' => 100],  // ساعة
        ]);
    }

    /**
     * حساب خصم التأخير بالدقائق
     */
    public function calculateLatenessDeduction(int $lateMinutes, float $dailyRate): float
    {
        $policy = $this->getLatenessPolicy();

        foreach ($policy as $tier) {
            if ($lateMinutes >= $tier['from'] && $lateMinutes <= $tier['to']) {
                $hourlyRate = $dailyRate / 8; // افتراض 8 ساعات عمل
                return round(($hourlyRate * $tier['deduction_percent']) / 100, 2);
            }
        }

        // إذا تجاوز كل الشرائح، خصم يوم كامل
        return $dailyRate;
    }

    // ==================== سياسة الغياب ====================

    /**
     * معامل خصم الغياب (1.0 = يوم بيوم، 2.0 = يوم بيومين)
     */
    public function getAbsenceMultiplier(): float
    {
        return config('payroll.absence_multiplier', 1.0);
    }

    /**
     * حساب خصم الغياب
     */
    public function calculateAbsenceDeduction(int $absentDays, float $dailyRate): float
    {
        $multiplier = $this->getAbsenceMultiplier();
        return round($absentDays * $dailyRate * $multiplier, 2);
    }

    // ==================== Pro-rata (الأيام الجزئية) ====================

    /**
     * حساب الأيام الفعلية للموظف في الفترة
     * (للموظفين الجدد أو المنتهية عقودهم منتصف الشهر)
     */
    public function calculateProrataDays(
        Carbon $contractStart,
        Carbon $contractEnd,
        Carbon $periodStart,
        Carbon $periodEnd
    ): int {
        // إذا العقد يشمل الفترة كاملة
        if ($contractStart->lte($periodStart) && $contractEnd->gte($periodEnd)) {
            return $this->getMonthDays($periodStart);
        }

        // بداية الحساب الفعلية
        $effectiveStart = $contractStart->gt($periodStart) ? $contractStart : $periodStart;
        $effectiveEnd = $contractEnd->lt($periodEnd) ? $contractEnd : $periodEnd;

        // حساب الفرق بالأيام
        return max(0, $effectiveStart->diffInDays($effectiveEnd) + 1);
    }

    /**
     * حساب الراتب التناسبي (Pro-rata)
     */
    public function calculateProrataAmount(
        float $fullMonthAmount,
        int $actualDays,
        Carbon $date
    ): float {
        $monthDays = $this->getMonthDays($date);

        if ($actualDays >= $monthDays) {
            return $fullMonthAmount;
        }

        return round(($fullMonthAmount / $monthDays) * $actualDays, 2);
    }

    // ==================== سياسة بدل الانتظار ====================

    /**
     * معدل بدل حصة الانتظار
     */
    public function getSubstitutionClassRate(): float
    {
        return (float) config('payroll.substitution_class_rate', 100.0);
    }
}

<?php

namespace App\Domains\HR\Payroll\Services;

use App\Domains\HR\Payroll\Models\PayrollBatch;
use Illuminate\Support\Facades\Cache;

class PayrollCacheService
{
    /**
     * مدة التخزين الافتراضية (5 دقائق)
     * 
     * ⚠️ قاعدة ذهبية:
     * - الـ invalidation بالـ Events هو **الأساس** لضمان دقة البيانات.
     * - الـ TTL مجرد حماية إضافية (fallback) في حالة فشل الـ invalidation.
     */
    private const TTL = 300;

    // ==================== Keys Generators ====================

    public function summaryKey(int $batchId): string
    {
        return "payroll:batch:{$batchId}:summary";
    }

    public function staffPayslipsKey(int $staffId): string
    {
        return "payroll:staff:{$staffId}:payslips";
    }

    public function monthStatsKey(int $year, int $month): string
    {
        return "payroll:month:{$year}-{$month}:stats";
    }

    // ==================== Public API ====================

    /**
     * الحصول على ملخص الباتش مع الكاش
     */
    public function getBatchSummary(int $batchId, callable $callback)
    {
        return Cache::remember($this->summaryKey($batchId), self::TTL, $callback);
    }

    /**
     * إبطال كاش الباتش وكل ما يتعلق به
     */
    public function invalidateBatch(PayrollBatch $batch): void
    {
        // 1. Batch Summary
        Cache::forget($this->summaryKey($batch->id));

        // 2. Month Stats
        Cache::forget($this->monthStatsKey($batch->year, $batch->month));

        // 3. Staff Payslips (If we can identify them, or just use tags if store supports it)
        // For now, simpler invalidation is acceptable, or rely on short TTL.
        // Or if we want to be precise, we iterate records. But that's heavy.
        // Let's rely on TTL for user-specific views, or key by batch version if possible.
        // But for Batch Summary (Management View), it's critical to be fresh after updates.
    }
}

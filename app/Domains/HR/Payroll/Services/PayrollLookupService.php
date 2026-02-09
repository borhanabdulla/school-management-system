<?php

namespace App\Domains\HR\Payroll\Services;

use App\Domains\HR\Payroll\Models\PayrollBatch;
use App\Domains\HR\Payroll\Models\PayrollRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * PayrollLookupService - خدمة البحث عن بيانات الرواتب
 */
class PayrollLookupService
{
    /**
     * جلب مسيرات الرواتب مع الفلترة
     */
    public function getPayrollBatches(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return PayrollBatch::query()
            ->withCount('records')

            // فلتر السنة
            ->when($filters['year'] ?? null, function (Builder $query, $year) {
                $query->where('year', $year);
            })

            // فلتر الشهر
            ->when($filters['month'] ?? null, function (Builder $query, $month) {
                $query->where('month', $month);
            })

            // فلتر الحالة
            ->when($filters['status'] ?? null, function (Builder $query, $status) {
                $query->where('status', $status);
            })

            ->latest('year')
            ->latest('month')
            ->paginate($perPage);
    }

    /**
     * جلب سجل رواتب موظف معين
     */
    public function getEmployeePayrollHistory(int $staffId, int $limit = 12)
    {
        return PayrollRecord::query()
            ->with('batch')
            ->where('staff_id', $staffId)
            ->whereHas('batch', function ($q) {
                $q->where('status', 'paid');
            })
            ->latest()
            ->limit($limit)
            ->get();
    }

    // ═══════════════════════════════════════════════════════════════
    // Observer Support - Cache Invalidation Methods
    // ═══════════════════════════════════════════════════════════════

    /**
     * إبطال كاش مسير رواتب محدد (للاستخدام من Observer)
     */
    public function invalidateBatch(int $batchId, ?int $year = null, ?int $month = null): void
    {
        // إبطال كاش المسير نفسه
        \Illuminate\Support\Facades\Cache::forget("payroll:batch:{$batchId}:full");

        // إبطال كاش الفترة
        if ($year && $month) {
            \Illuminate\Support\Facades\Cache::forget("payroll:batch:{$year}:{$month}");
        }
    }
}

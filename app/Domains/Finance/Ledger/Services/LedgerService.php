<?php

namespace App\Domains\Finance\Ledger\Services;

use App\Domains\Finance\Ledger\Data\LedgerEntryData;
use App\Domains\Finance\Ledger\Enums\LedgerStatus;
use App\Domains\Finance\Ledger\Models\LedgerEntry;
use Illuminate\Support\Facades\DB;

/**
 * LedgerService - الخدمة الرئيسية لإدارة السجل المالي
 * 
 * ⚠️ قواعد ذهبية:
 * 1. Idempotency: نفس الـ external_key لا يُسجل مرتين
 * 2. Atomic: كل عملية داخل Transaction
 * 3. No Delete/Edit: القيد لا يُحذف ولا يُعدل، بل يُلغى فقط
 */
class LedgerService
{
    /**
     * تسجيل قيد جديد (دخل أو خرج)
     * 
     * @param LedgerEntryData $data بيانات القيد
     * @return LedgerEntry|null القيد الجديد أو null إذا كان موجوداً مسبقاً
     */
    public function record(LedgerEntryData $data): ?LedgerEntry
    {
        return DB::transaction(function () use ($data) {
            // Idempotency Check: إذا كان القيد موجوداً، لا نسجله مرة أخرى
            $existing = LedgerEntry::where('external_key', $data->externalKey)->first();

            if ($existing) {
                // القيد موجود مسبقاً، نعيده بدون إنشاء جديد
                return null;
            }

            return LedgerEntry::create($data->toArray());
        });
    }

    /**
     * تسجيل قيد دخل
     */
    public function recordIncome(LedgerEntryData $data): ?LedgerEntry
    {
        return $this->record($data);
    }

    /**
     * تسجيل قيد خرج
     */
    public function recordExpense(LedgerEntryData $data): ?LedgerEntry
    {
        return $this->record($data);
    }

    /**
     * إلغاء قيد بالـ external_key
     * 
     * @param string $externalKey المفتاح الفريد
     * @param int $cancelledBy ID المستخدم الذي ألغى
     * @param string $reason سبب الإلغاء
     * @return LedgerEntry|null القيد الملغى أو null إذا لم يوجد
     */
    public function cancelByExternalKey(
        string $externalKey,
        int $cancelledBy,
        string $reason
    ): ?LedgerEntry {
        return DB::transaction(function () use ($externalKey, $cancelledBy, $reason) {
            $entry = LedgerEntry::where('external_key', $externalKey)
                ->where('status', LedgerStatus::Posted)
                ->first();

            if (!$entry) {
                return null;
            }

            $entry->update([
                'status' => LedgerStatus::Cancelled,
                'cancelled_at' => now(),
                'cancelled_by' => $cancelledBy,
                'cancel_reason' => $reason,
            ]);

            return $entry->fresh();
        });
    }

    /**
     * إلغاء قيد بالمصدر (source_type + source_id)
     */
    public function cancelBySource(
        string $sourceType,
        int $sourceId,
        int $cancelledBy,
        string $reason
    ): ?LedgerEntry {
        return DB::transaction(function () use ($sourceType, $sourceId, $cancelledBy, $reason) {
            $entry = LedgerEntry::where('source_type', $sourceType)
                ->where('source_id', $sourceId)
                ->where('status', LedgerStatus::Posted)
                ->first();

            if (!$entry) {
                return null;
            }

            $entry->update([
                'status' => LedgerStatus::Cancelled,
                'cancelled_at' => now(),
                'cancelled_by' => $cancelledBy,
                'cancel_reason' => $reason,
            ]);

            return $entry->fresh();
        });
    }

    // ==================== استعلامات التقارير ====================

    /**
     * حساب صافي الكاش لفترة معينة
     */
    public function getNetCashForPeriod($start, $end): float
    {
        $totals = LedgerEntry::posted()
            ->forPeriod($start, $end)
            ->selectRaw('
                SUM(CASE WHEN direction = "in" THEN amount ELSE 0 END) as total_in,
                SUM(CASE WHEN direction = "out" THEN amount ELSE 0 END) as total_out
            ')
            ->first();

        return (float) (($totals->total_in ?? 0) - ($totals->total_out ?? 0));
    }

    /**
     * ملخص الكاش لفترة معينة
     */
    public function getCashSummaryForPeriod($start, $end): array
    {
        $totals = LedgerEntry::posted()
            ->forPeriod($start, $end)
            ->selectRaw('
                SUM(CASE WHEN direction = "in" THEN amount ELSE 0 END) as total_in,
                SUM(CASE WHEN direction = "out" THEN amount ELSE 0 END) as total_out
            ')
            ->first();

        $in = (float) ($totals->total_in ?? 0);
        $out = (float) ($totals->total_out ?? 0);

        return [
            'total_in' => $in,
            'total_out' => $out,
            'net_cash' => $in - $out,
        ];
    }
}

<?php

namespace App\Domains\HR\Payroll\Services;

use App\Domains\HR\Payroll\Models\Contract;
use App\Domains\HR\Payroll\Models\ContractItem;
use App\Domains\HR\Payroll\Enums\ContractStatus;
use App\Domains\HR\Payroll\Exceptions\PeriodLockedException;
use App\Domains\HR\Payroll\Services\PayrollPeriodLockService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ContractService
{
    public function __construct(
        private PayrollPeriodLockService $periodLockService
    ) {
    }
    /**
     *الحصول على جميع العقود النشطة لأحد الموظفين خلال فترة محددة.
     * هذا يتعامل مع سيناريو "انقسام الشهر" حيث قد يكون للموظف عدة عقود متتابعة في نفس الشهر.
     * 
     * @return Collection<Contract>
     */
    public function getContractsForPeriod(int $staffId, Carbon $start, Carbon $end): Collection
    {
        return Contract::query()
            ->where('staff_id', $staffId)->where('status', ContractStatus::Active)
            ->where(function ($query) use ($start, $end) {
                // Contract overlaps with the period
                $query->where('start_date', '<=', $end)->where('end_date', '>=', $start);
            })->with([
                    'contractItems' => function ($q) {
                        $q->active();
                    }
                ])->orderBy('start_date')->get();
    }

    /**
     *إنشاء عقد جديد وتمييز العقد القديم (سلسلة).
     */
    public function createContract(array $data, array $items = []): Contract
    {
        return DB::transaction(function () use ($data, $items) {
            $staffId = $data['staff_id'];
            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);

            $lockingBatch = $this->periodLockService->getLockingBatchForRange($startDate, $endDate);
            if ($lockingBatch) {
                throw new PeriodLockedException(
                    $lockingBatch->period_start->toDateString(),
                    $lockingBatch->period_end->toDateString(),
                    $lockingBatch->status->value,
                    $lockingBatch->id
                );
            }

            // 1. ابحث عن العقد الحالي النشط وأنهِه / أرشفه
            $currentContract = Contract::where('staff_id', $staffId)
                ->where('status', ContractStatus::Active)->where('end_date', '>=', $startDate)->first();

            if ($currentContract) {
                $currentContract->ensureEditable();
                // If the new contract starts after the current one began
                if ($startDate->gt($currentContract->start_date)) {
                    // Cap the old contract to end the day before the new one starts
                    $currentContract->update([
                        'end_date' => $startDate->copy()->subDay(),
                        // We keep it active as it's still valid history, 
                        // but effectively "expired" for future dates.
                        // Or we can set it to 'expired' if end_date < now.
                    ]);
                } else {
                    // If new contract starts on or before current, we might be correcting history
                    // For now, let's assume we just terminate the old one.
                    $currentContract->update(['status' => ContractStatus::Terminated]);
                }
            }

            // 2. Create the new contract
            $contract = Contract::create($data);

            // 3. Add Items
            foreach ($items as $item) {
                $contract->contractItems()->create($item);
            }

            return $contract;
        });
    }
}

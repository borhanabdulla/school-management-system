<?php

namespace App\Domains\Academic\Promotion\Listeners;

use App\Domains\Academic\Promotion\Models\Promotion;
use App\Domains\Finance\Events\PaymentReceived;
use App\Domains\Finance\Events\PaymentCancelled;
use App\Domains\Finance\Services\StudentFinancialClearanceService;
use App\Domains\Academic\Grading\Models\SystemSetting;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdatePromotionClearanceListener implements ShouldQueue
{
    public function __construct(
        private StudentFinancialClearanceService $clearanceService
    ) {
    }

    public function handle($event): void
    {
        // Handle both PaymentReceived and PaymentCancelled
        if (!isset($event->invoice)) {
            return;
        }

        $invoice = $event->invoice;
        $studentId = $invoice->student_id;
        $yearId = $invoice->academic_year_id;

        // Find existing promotion for this student and year
        // We look for promotions WHERE the academic_year_id matches the invoice year
        // (meaning: the year they were promoted FROM).
        $promotion = Promotion::where('student_id', $studentId)
            ->where('academic_year_id', $yearId)
            ->where('is_reverted', false)
            ->first();

        if (!$promotion) {
            return;
        }

        // Re-check financial clearance
        $clearance = $this->clearanceService->checkClearance($studentId, $yearId);
        $isCleared = $clearance['is_cleared'];

        // Determine if blocked based on system setting
        $shouldBlock = !$isCleared && SystemSetting::get('promotion.require_financial_clearance_for_certificate', true);

        // Update the record if changed
        if ($promotion->has_financial_clearance !== $isCleared || $promotion->certificate_blocked !== $shouldBlock) {
            $promotion->update([
                'has_financial_clearance' => $isCleared,
                'certificate_blocked' => $shouldBlock
            ]);
        }
    }
}

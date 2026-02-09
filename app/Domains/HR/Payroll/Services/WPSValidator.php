<?php

namespace App\Domains\HR\Payroll\Services;

use App\Domains\HR\Payroll\Models\PayrollBatch;

class WPSValidator
{
    /**
     * Validate a batch for WPS compliance.
     * Returns an array of errors/warnings.
     */
    public function validateBatch(PayrollBatch $batch): array
    {
        $issues = [];
        $records = $batch->records()->with('contract.staff')->get();

        foreach ($records as $record) {
            $staff = $record->contract->staff;

            // Check 1: Missing IBAN
            if (empty($staff->bank_iban)) {
                $issues[] = [
                    'type' => 'error',
                    'staff_id' => $staff->id,
                    'staff_name' => $staff->full_name,
                    'message' => 'رقم الآيبان مفقود (Missing IBAN)',
                ];
            }

            // Check 2: Zero Net Salary
            if ($record->net_payable <= 0) {
                $issues[] = [
                    'type' => 'warning',
                    'staff_id' => $staff->id,
                    'staff_name' => $staff->full_name,
                    'message' => 'صافي الراتب صفر أو سالب',
                ];
            }

            // Check 3: Missing National ID (assuming field exists or using placeholder)
            // if (empty($staff->national_id)) { ... }
        }

        return $issues;
    }
}

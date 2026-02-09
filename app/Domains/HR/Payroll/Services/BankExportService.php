<?php

namespace App\Domains\HR\Payroll\Services;

use App\Domains\HR\Payroll\Models\PayrollBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class BankExportService
{
    /**
     * توليد محتوى CSV لنظام WPS لدُفعة الرواتب.
     * التنسيق: SIF (التنسيق الصناعي القياسي) أو CSV بسيط حسب متطلبات البنك المحلي.
     * سنقوم بتنفيذ تنسيق CSV قياسي شائع الاستخدام:
     * رقم الآيبان، المبلغ، اسم الموظف، رقم هوية الموظف، المرجع
     */
    public function generateCsv(PayrollBatch $batch): string
    {
        $records = $batch->records()->with('contract.staff')->get();

        // Header
        $csv = "IBAN,Amount,Currency,Employee Name,Employee ID,Reference,Description\n";

        foreach ($records as $record) {
            $staff = $record->contract->staff;
            $iban = $staff->bank_iban ?? '';
            $amount = number_format($record->net_payable, 2, '.', '');
            $name = $staff->full_name;
            $empId = $staff->id; // Or national ID if available
            $ref = "SAL-" . $batch->year . "-" . $batch->month . "-" . $empId;
            $desc = "Salary " . $batch->period_label;

            // Sanitize
            $name = str_replace(',', ' ', $name);
            $desc = str_replace(',', ' ', $desc);

            $csv .= "{$iban},{$amount},SAR,{$name},{$empId},{$ref},{$desc}\n";
        }

        return $csv;
    }

    /**
     * Export to a file and return the path.
     */
    public function exportToFile(PayrollBatch $batch): string
    {
        $content = $this->generateCsv($batch);
        $filename = 'payroll-export-' . $batch->id . '-' . time() . '.csv';
        $path = 'exports/' . $filename;

        Storage::disk('local')->put($path, $content);

        return $path;
    }
}

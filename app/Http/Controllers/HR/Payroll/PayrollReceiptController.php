<?php

namespace App\Http\Controllers\HR\Payroll;

use App\Http\Controllers\Controller;
use App\Domains\HR\Payroll\Models\PayrollBatch;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PayrollReceiptController extends Controller
{
    public function show(PayrollBatch $batch)
    {
        // التحقق من أن الدفعة مدفوعة أو على الأقل معتمدة (لأغراض العرض)
        if ($batch->status === \App\Domains\HR\Payroll\Enums\PayrollBatchStatus::Draft) {
            abort(403, 'لا يمكن طباعة إيصال لدفعة مسودة.');
        }

        // تحميل العلاقات
        $batch->load(['records.staff', 'paidByUser', 'approvedByUser']);

        // إحصائيات سريعة للعرض
        $stats = [
            'count' => $batch->records->count(),
            'total_basic' => $batch->records->sum('basic_salary'),
            'total_deductions' => $batch->records->sum('total_deductions'),
            'total_net' => $batch->records->sum('net_payable'),
        ];

        // تجهيز PDF
        $pdf = Pdf::loadView('payroll.receipts.batch-payout', [
            'batch' => $batch,
            'stats' => $stats,
            'schoolName' => config('app.name', 'مؤسسة التعليم المتطور'),
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream("payroll-payout-{$batch->id}.pdf");
    }
}

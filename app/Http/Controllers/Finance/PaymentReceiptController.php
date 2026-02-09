<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Domains\Finance\Models\Payment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentReceiptController extends Controller
{
    public function show(Payment $payment)
    {
        // التأكد من تحميل العلاقات الضرورية
        $payment->load(['invoice.student.guardians', 'invoice.student.grade', 'invoice.academicYear']);

        // تجهيز PDF
        // نستخدم loadView لتحميل الـ Blade وتمرير البيانات
        $pdf = Pdf::loadView('finance.receipts.student-payment', [
            'payment' => $payment,
            'invoice' => $payment->invoice,
            'student' => $payment->invoice->student,
            'schoolName' => config('app.name', 'مؤسسة التعليم المتطور'), // يمكن استبداله بإعدادات
        ]);

        // إعدادات الورقة (A5 Landscape أو A4 حسب الرغبة - A5 مناسب للإيصالات)
        $pdf->setPaper('a5', 'landscape');

        // عرض في المتصفح (stream) بدلاً من التحميل المباشر (download)
        return $pdf->stream("receipt-{$payment->transaction_reference}.pdf");
    }
}

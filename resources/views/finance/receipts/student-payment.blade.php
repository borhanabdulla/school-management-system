<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>إيصال قبض - #{{ $payment->transaction_reference }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif; /* Supports basic Arabic */
            direction: rtl;
            text-align: right;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        .school-info {
            font-size: 14px;
            color: #777;
            margin-top: 5px;
        }
        .receipt-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            background-color: #f8f9fa;
            padding: 10px;
            border: 1px solid #ddd;
        }
        .meta-table, .details-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 8px;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            color: #555;
            width: 120px;
        }
        .amount-box {
            border: 2px solid #2ecc71;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #27ae60;
            border-radius: 8px;
            margin: 10px 0;
        }
        .footer {
            margin-top: 50px;
            border-top: 1px solid #eee;
            padding-top: 20px;
            font-size: 12px;
            color: #999;
            text-align: center;
        }
        .signatures {
            margin-top: 40px;
            width: 100%;
        }
        .signatures td {
            text-align: center;
            width: 50%;
            padding-top: 40px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">{{ $schoolName }}</div>
        <div class="school-info">نظام الإدارة المدرسية المتطور</div>
    </div>

    <div class="receipt-title">إيصال قبض (Payment Receipt)</div>

    <table class="meta-table">
        <tr>
            <td class="label">رقم السند:</td>
            <td>#{{ $payment->transaction_reference }}</td>
            <td class="label">التاريخ:</td>
            <td>{{ $payment->payment_date->format('Y/m/d') }}</td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td class="label">استلمنا من:</td>
            <td>
                <strong>{{ $invoice->student->full_name_ar }}</strong>
                @if($invoice->student->grade)
                    <span style="font-size: 12px; color: #666;">({{ $invoice->student->grade->name_ar }})</span>
                @endif
                <br>
                <span style="font-size: 12px; color: #888;">الولي: {{ $invoice->student->guardian->full_name ?? '-' }}</span>
            </td>
        </tr>
    </table>

    <div class="amount-box">
        {{ number_format($payment->amount, 2) }} ر.س
    </div>
    
    <div style="text-align: center; margin-bottom: 20px; font-size: 14px;">
        (فقط {{ $payment->amount }} ريال لا غير)
    </div>

    <table class="meta-table">
        <tr>
            <td class="label">وذلك عن:</td>
            <td>سداد مستحقات للفاتورة رقم <strong>#{{ $invoice->id }}</strong></td>
        </tr>
        <tr>
            <td class="label">طريقة الدفع:</td>
            <td>{{ $payment->method->label() ?? $payment->method }}</td>
        </tr>
        @if($payment->notes)
            <tr>
                <td class="label">ملاحظات:</td>
                <td>{{ $payment->notes }}</td>
            </tr>
        @endif
    </table>

    <table class="signatures">
        <tr>
            <td>
                <strong>المحاسب</strong>
                <br>
                __________________
            </td>
            <td>
                <strong>الختم</strong>
                <br>
                <br>
            </td>
        </tr>
    </table>

    <div class="footer">
        تم استخراج هذا الإيصال آلياً من النظام بتاريخ {{ now()->format('Y/m/d H:i') }}
        <br>
        معرف الحركة: {{ $payment->id }}
    </div>
</body>
</html>

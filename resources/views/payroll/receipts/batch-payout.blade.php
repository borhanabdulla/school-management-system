<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>إشعار صرف رواتب - {{ $batch->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            direction: rtl;
            text-align: right;
            padding: 30px;
            color: #333;
            font-size: 14px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }
        .title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .summary-box {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #ddd;
            margin-bottom: 30px;
        }
        .signatures {
            margin-top: 60px;
            border: none;
        }
        .signatures td {
            border: none;
            padding-top: 50px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $schoolName }}</h2>
        <p>تقرير صرف الرواتب الشهرية</p>
    </div>

    <div class="title">إشعار صرف دفعة رواتب: {{ $batch->name }}</div>

    <div class="summary-box">
        <table style="margin: 0; border: none;">
            <tr style="background: none;">
                <td style="border: none; text-align: right;"><strong>رقم الدفعة:</strong> #{{ $batch->id }}</td>
                <td style="border: none; text-align: right;"><strong>الفترة:</strong> {{ $batch->period_label }}</td>
                <td style="border: none; text-align: right;"><strong>تاريخ الصرف:</strong> {{ $batch->paid_at?->format('Y/m/d') ?? '-' }}</td>
            </tr>
            <tr style="background: none;">
                <td style="border: none; text-align: right;"><strong>عدد الموظفين:</strong> {{ $stats['count'] }}</td>
                <td style="border: none; text-align: right;"><strong>طريقة الدفع:</strong> {{ $batch->payout_method?->label() ?? '-' }}</td>
                <td style="border: none; text-align: right;"><strong>المرجع:</strong> {{ $batch->payout_reference ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <h3>ملخص المبالغ</h3>
    <table>
        <thead>
            <tr>
                <th>إجمالي الرواتب الأساسية</th>
                <th>إجمالي الاستقطاعات</th>
                <th>صافي المبلغ المصروف</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ number_format($stats['total_basic'], 2) }}</td>
                <td style="color: #c0392b;">{{ number_format($stats['total_deductions'], 2) }}</td>
                <td style="font-weight: bold; font-size: 16px; color: #27ae60;">{{ number_format($stats['total_net'], 2) }} ر.س</td>
            </tr>
        </tbody>
    </table>

    <h3>الموافقات</h3>
    <table class="signatures">
        <tr>
            <td>
                <strong>تم الإعداد بواسطة</strong>
                <br>
                النظام الآلي
            </td>
            <td>
                <strong>تم الاعتماد بواسطة</strong>
                <br>
                {{ $batch->approvedByUser?->name ?? 'مدير النظام' }}
                <br>
                <small>{{ $batch->approved_at?->format('Y/m/d') }}</small>
            </td>
            <td>
                <strong>المحاسب المسؤول (تم الصرف)</strong>
                <br>
                {{ $batch->paidByUser?->name ?? '-' }}
                <br>
                <small>{{ $batch->paid_at?->format('Y/m/d') }}</small>
            </td>
        </tr>
    </table>

    <div class="footer">
        تم استخراج هذا التقرير من النظام المالي والمحاسبي المعتمد بتاريخ {{ now()->format('Y/m/d H:i') }}
    </div>
</body>
</html>

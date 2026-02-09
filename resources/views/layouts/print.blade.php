<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'طباعة' }} | نظام المدرسة</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.4;
            direction: rtl;
            background: rgb(var(--color-white));
        }
        .container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 10mm;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid rgb(var(--print-border));
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18pt;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 10pt;
            color: rgb(var(--print-muted));
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid rgb(var(--print-border));
            padding: 8px 12px;
            text-align: right;
        }
        th {
            background-color: rgb(var(--print-table-header));
            font-weight: bold;
        }
        .no-print {
            margin: 20px 0;
            text-align: center;
        }
        .no-print button {
            padding: 10px 30px;
            font-size: 14pt;
            cursor: pointer;
            background: rgb(var(--print-button));
            color: rgb(var(--color-white));
            border: none;
            border-radius: 8px;
            margin: 0 5px;
        }
        .no-print button:hover {
            background: rgb(var(--print-button-hover));
        }
        .sticker-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 20px;
        }
        .sticker {
            border: 2px dashed rgb(var(--print-border));
            padding: 15px;
            text-align: center;
            page-break-inside: avoid;
        }
        .sticker .secret {
            font-size: 24pt;
            font-weight: bold;
            font-family: monospace;
            letter-spacing: 3px;
        }
        .sticker .seat {
            font-size: 12pt;
            color: rgb(var(--print-muted));
            margin-top: 5px;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: rgb(var(--color-white)); }
            .container { max-width: 100%; padding: 5mm; }
        }
        @page {
            size: A4;
            margin: 10mm;
        }
    </style>
</head>
<body>
    <div class="container">
        {{ $slot }}
    </div>
</body>
</html>

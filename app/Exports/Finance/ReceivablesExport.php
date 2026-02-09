<?php

namespace App\Exports\Finance;

use App\Domains\Finance\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReceivablesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        return Invoice::query()
            ->with(['student.guardians', 'academicYear'])
            ->where('remaining_amount', '>', 0)
            ->where('status', '!=', \App\Domains\Finance\Enums\InvoiceStatus::Cancelled)
            ->orderBy('due_date', 'asc');
    }

    public function headings(): array
    {
        return [
            'رقم الفاتورة',
            'تاريخ الاستحقاق',
            'الطالب',
            'ولي الأمر',
            'السنة الدراسية',
            'المبلغ الإجمالي',
            'المدفوع',
            'المتبقي (المستحق)',
            'الحالة',
        ];
    }

    public function map($invoice): array
    {
        return [
            $invoice->id,
            $invoice->due_date->format('Y-m-d'),
            $invoice->student->full_name_ar ?? $invoice->student->first_name,
            $invoice->student->guardian->full_name ?? 'غير محدد',
            $invoice->academicYear->name ?? '-',
            number_format($invoice->total_amount, 2),
            number_format($invoice->paid_amount, 2),
            number_format($invoice->remaining_amount, 2),
            $invoice->status->label() ?? $invoice->status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

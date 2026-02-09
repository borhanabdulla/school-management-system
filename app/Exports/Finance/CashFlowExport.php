<?php

namespace App\Exports\Finance;

use App\Domains\Finance\Ledger\Models\LedgerEntry;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CashFlowExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        return LedgerEntry::query()
            ->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'رقم العملية',
            'التاريخ',
            'النوع',
            'المبلغ',
            'الرصيد بعد العملية',
            'الوصف',
        ];
    }

    public function map($entry): array
    {
        return [
            $entry->id,
            $entry->created_at->format('Y-m-d H:i'),
            $entry->type === 'credit' ? 'إيداع (قبض)' : 'سحب (صرف)',
            number_format($entry->amount, 2),
            number_format($entry->balance_after, 2),
            $entry->description,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

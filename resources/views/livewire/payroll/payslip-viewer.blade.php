<div class="py-8 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="max-w-[210mm] mx-auto bg-white dark:bg-gray-800 shadow-xl rounded-none sm:rounded-xl overflow-hidden print:shadow-none print:rounded-none print:w-full print:max-w-none">
        
        {{-- Toolbar (Hidden in Print) --}}
        <div class="p-4 bg-gray-800 text-white flex justify-between items-center print:hidden">
            <div class="flex items-center gap-4">
                <a href="{{ route('payroll.batches.index') }}" class="text-gray-300 hover:text-white flex items-center gap-1 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    عودة
                </a>
                <h1 class="font-bold text-lg">معاينة قسيمة الراتب</h1>
            </div>
            <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg flex items-center gap-2 transition-colors shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                طباعة / تحميل PDF
            </button>
        </div>

        {{-- Payslip Content --}}
        <div class="p-8 sm:p-12 relative">
            {{-- Watermark --}}
            <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none select-none overflow-hidden">
                <svg class="w-[150%] h-[150%] text-gray-900 transform -rotate-12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
            </div>

            {{-- Official Header --}}
            <div class="flex justify-between items-start border-b-2 border-gray-900 pb-6 mb-8">
                <div class="text-right">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">المدرسة الذكية الحديثة</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">قسم الموارد البشرية والرواتب</p>
                    <p class="text-sm text-gray-500 mt-1">الرياض، المملكة العربية السعودية</p>
                </div>
                <div class="text-left">
                    <div class="w-20 h-20 bg-gray-100 rounded-lg flex items-center justify-center border-2 border-gray-200">
                        <span class="text-gray-400 font-bold text-xs">LOGO</span>
                    </div>
                </div>
            </div>

            {{-- Title --}}
            <div class="text-center mb-8">
                <h1 class="text-3xl font-black text-gray-900 dark:text-white uppercase tracking-widest border-2 border-gray-900 inline-block px-8 py-2 rounded-lg">
                    قسيمة راتب
                </h1>
                <p class="text-gray-500 mt-2 font-medium">{{ $record->batch->period_label }}</p>
            </div>

            {{-- Employee Details Grid --}}
            <div class="grid grid-cols-2 gap-x-12 gap-y-4 mb-8 text-sm">
                <div class="flex justify-between border-b border-gray-200 pb-1">
                    <span class="text-gray-500">اسم الموظف</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $record->staff?->full_name }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-200 pb-1">
                    <span class="text-gray-500">الرقم الوظيفي</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $record->staff?->id }}</span> {{-- Or employee_id --}}
                </div>
                <div class="flex justify-between border-b border-gray-200 pb-1">
                    <span class="text-gray-500">المسمى الوظيفي</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $record->staff?->job_title ?? '-' }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-200 pb-1">
                    <span class="text-gray-500">تاريخ الإصدار</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $record->created_at->format('Y/m/d') }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-200 pb-1">
                    <span class="text-gray-500">أيام العمل</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $record->actual_working_days ?? 30 }} يوم</span>
                </div>
                <div class="flex justify-between border-b border-gray-200 pb-1">
                    <span class="text-gray-500">طريقة الدفع</span>
                    <span class="font-bold text-gray-900 dark:text-white">تحويل بنكي</span>
                </div>
            </div>

            {{-- Financial Table --}}
            <div class="border-2 border-gray-900 rounded-lg overflow-hidden mb-8">
                <div class="grid grid-cols-2 divide-x divide-x-reverse divide-gray-900 bg-gray-100 print:bg-gray-100">
                    <div class="p-3 text-center font-bold text-gray-900 border-b border-gray-900">الاستحقاقات (Earnings)</div>
                    <div class="p-3 text-center font-bold text-gray-900 border-b border-gray-900">الاستقطاعات (Deductions)</div>
                </div>
                
                <div class="grid grid-cols-2 divide-x divide-x-reverse divide-gray-900">
                    {{-- Earnings Column --}}
                    <div class="p-0">
                        <table class="w-full">
                            @foreach($this->earnings as $item)
                                <tr class="border-b border-gray-200 last:border-0">
                                    <td class="p-3 text-sm text-gray-700">{{ $item->description }}</td>
                                    <td class="p-3 text-sm font-bold text-gray-900 text-left">{{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @endforeach
                            {{-- Fill empty space if needed --}}
                            @for($i = 0; $i < max(0, 5 - $this->earnings->count()); $i++)
                                <tr class="border-b border-gray-100 last:border-0"><td class="p-3">&nbsp;</td><td class="p-3">&nbsp;</td></tr>
                            @endfor
                        </table>
                    </div>

                    {{-- Deductions Column --}}
                    <div class="p-0 bg-red-50/30 print:bg-transparent">
                        <table class="w-full">
                            @foreach($this->deductions as $item)
                                <tr class="border-b border-gray-200 last:border-0">
                                    <td class="p-3 text-sm text-gray-700">{{ $item->description }}</td>
                                    <td class="p-3 text-sm font-bold text-red-600 text-left">{{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>

                {{-- Totals Row --}}
                <div class="grid grid-cols-2 divide-x divide-x-reverse divide-gray-900 border-t-2 border-gray-900 bg-gray-50 print:bg-gray-50">
                    <div class="p-3 flex justify-between items-center">
                        <span class="font-bold text-gray-700">إجمالي الاستحقاقات</span>
                        <span class="font-bold text-gray-900">{{ number_format($record->total_earnings, 2) }}</span>
                    </div>
                    <div class="p-3 flex justify-between items-center">
                        <span class="font-bold text-gray-700">إجمالي الاستقطاعات</span>
                        <span class="font-bold text-red-600">{{ number_format($record->total_deductions, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Net Salary --}}
            <div class="flex justify-end mb-12">
                <div class="w-1/2 bg-gray-900 text-white p-4 rounded-lg flex justify-between items-center print:bg-gray-900 print:text-white">
                    <span class="text-lg font-medium">صافي الراتب (Net Salary)</span>
                    <span class="text-2xl font-bold">{{ number_format($record->net_payable, 2) }} ر.س</span>
                </div>
            </div>

            {{-- Signatures --}}
            <div class="grid grid-cols-2 gap-20 mt-16 pt-8 border-t border-gray-200 page-break-inside-avoid">
                <div class="text-center">
                    <p class="font-bold text-gray-900 mb-12">توقيع الموظف</p>
                    <div class="border-t border-gray-400 w-2/3 mx-auto"></div>
                </div>
                <div class="text-center">
                    <p class="font-bold text-gray-900 mb-12">المدير المالي / الختم</p>
                    <div class="border-t border-gray-400 w-2/3 mx-auto"></div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="mt-12 text-center text-xs text-gray-400 print:fixed print:bottom-4 print:left-0 print:right-0">
                تم إصدار هذا المستند إلكترونياً من خلال نظام المدرسة الذكية - {{ now()->format('Y/m/d H:i') }}
            </div>
        </div>
    </div>

    {{-- Print Styles --}}
    <style>
        @media print {
            @page { margin: 0; size: A4; }
            body { background: white !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .print\:hidden { display: none !important; }
            .print\:shadow-none { box-shadow: none !important; }
            .print\:rounded-none { border-radius: 0 !important; }
            .print\:w-full { width: 100% !important; }
            .print\:max-w-none { max-width: none !important; }
            .print\:bg-gray-100 { background-color: #f3f4f6 !important; }
            .print\:bg-gray-50 { background-color: #f9fafb !important; }
            .print\:bg-gray-900 { background-color: #111827 !important; }
            .print\:text-white { color: white !important; }
            .print\:fixed { position: fixed; }
            .print\:bottom-4 { bottom: 1rem; }
        }
    </style>
</div>

<div x-data="{ activeTab: 'overview' }" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    {{-- Header & Tabs --}}
    <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
        <div class="px-6 py-4 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                الملف المالي
            </h3>
            @if($activeContract)
                @php
                    $daysRemaining = now()->diffInDays($activeContract->end_date, false);
                    $statusColor = $daysRemaining > 60 ? 'bg-green-100 text-green-800' : ($daysRemaining > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                    $statusText = $daysRemaining > 60 ? 'عقد ساري' : ($daysRemaining > 0 ? 'ينتهي قريباً' : 'منتهي');
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                    {{ $statusText }}
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    لا يوجد عقد نشط
                </span>
            @endif
        </div>
        
        {{-- Tabs Navigation --}}
        <div class="flex overflow-x-auto px-6 gap-6 scrollbar-hide">
            <button @click="activeTab = 'overview'" 
                :class="{ 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400': activeTab === 'overview', 'text-gray-500 border-transparent hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'overview' }"
                class="whitespace-nowrap pb-3 border-b-2 font-medium text-sm transition-colors">
                نظرة عامة
            </button>
            <button @click="activeTab = 'contracts'" 
                :class="{ 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400': activeTab === 'contracts', 'text-gray-500 border-transparent hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'contracts' }"
                class="whitespace-nowrap pb-3 border-b-2 font-medium text-sm transition-colors">
                العقود
            </button>
            <button @click="activeTab = 'payrolls'" 
                :class="{ 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400': activeTab === 'payrolls', 'text-gray-500 border-transparent hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'payrolls' }"
                class="whitespace-nowrap pb-3 border-b-2 font-medium text-sm transition-colors">
                الرواتب والدفعات
            </button>
            <button @click="activeTab = 'loans'" 
                :class="{ 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400': activeTab === 'loans', 'text-gray-500 border-transparent hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'loans' }"
                class="whitespace-nowrap pb-3 border-b-2 font-medium text-sm transition-colors">
                السلف والقروض
            </button>
            <button @click="activeTab = 'variations'" 
                :class="{ 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400': activeTab === 'variations', 'text-gray-500 border-transparent hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'variations' }"
                class="whitespace-nowrap pb-3 border-b-2 font-medium text-sm transition-colors">
                المتغيرات
            </button>
            <button @click="activeTab = 'bank'" 
                :class="{ 'text-indigo-600 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400': activeTab === 'bank', 'text-gray-500 border-transparent hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'bank' }"
                class="whitespace-nowrap pb-3 border-b-2 font-medium text-sm transition-colors">
                المعلومات البنكية
            </button>
        </div>
    </div>

    <div class="p-6">
        {{-- 1. Overview Tab (Status Card) --}}
        <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            @if($activeContract)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-100 dark:border-gray-600">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">الراتب الأساسي</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($activeContract->basic_salary, 0) }} <span class="text-xs font-normal text-gray-500">ر.س</span></p>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-100 dark:border-green-800">
                        <p class="text-sm text-green-600 dark:text-green-400 mb-1">إجمالي البدلات</p>
                        <p class="text-2xl font-bold text-green-700 dark:text-green-300">+{{ number_format($activeContract->total_allowances, 0) }} <span class="text-xs font-normal text-green-600">ر.س</span></p>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border border-red-100 dark:border-red-800">
                        <p class="text-sm text-red-600 dark:text-red-400 mb-1">الاستقطاعات الثابتة</p>
                        <p class="text-2xl font-bold text-red-700 dark:text-red-300">-{{ number_format($activeContract->contractItems->where('type', 'deduction')->sum('amount'), 0) }} <span class="text-xs font-normal text-red-600">ر.س</span></p>
                    </div>
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-lg border border-indigo-100 dark:border-indigo-800">
                        <p class="text-sm text-indigo-600 dark:text-indigo-400 mb-1">صافي الراتب المتوقع</p>
                        <p class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">
                            {{ number_format($activeContract->basic_salary + $activeContract->total_allowances - $activeContract->contractItems->where('type', 'deduction')->sum('amount'), 0) }} 
                            <span class="text-xs font-normal text-indigo-600">ر.س</span>
                        </p>
                    </div>
                </div>

                {{-- Contract Expiry Alert --}}
                @php
                    $daysRemaining = now()->diffInDays($activeContract->end_date, false);
                @endphp
                @if($daysRemaining <= 60 && $daysRemaining > 0)
                    <div class="flex items-center p-4 mb-4 text-sm text-yellow-800 border border-yellow-300 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300 dark:border-yellow-800" role="alert">
                        <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                        </svg>
                        <span class="sr-only">Info</span>
                        <div>
                            <span class="font-medium">تنبيه!</span> ينتهي العقد الحالي خلال {{ $daysRemaining }} يوم ({{ $activeContract->end_date->format('Y-m-d') }}).
                        </div>
                    </div>
                @endif
            @else
                <div class="text-center py-12">
                    <div class="h-16 w-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">لا يوجد عقد نشط</h3>
                    <p class="mt-1 text-gray-500 dark:text-gray-400">يجب إنشاء عقد للموظف لبدء حساب الرواتب.</p>
                    <div class="mt-6">
                        <a href="{{ route('payroll.contracts.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            إنشاء عقد جديد
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- 2. Contracts Tab --}}
        <div x-show="activeTab === 'contracts'" style="display: none;">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-base font-medium text-gray-900 dark:text-white">سجل العقود</h4>
                <a href="{{ route('payroll.contracts.index') }}" class="inline-flex items-center px-3 py-1.5 border border-indigo-600 text-indigo-600 rounded-lg text-xs font-medium hover:bg-indigo-50 transition-colors">
                    <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    تعديل / ترقية العقد
                </a>
            </div>

            @if($activeContract)
                <div class="bg-white dark:bg-gray-800 border border-green-200 dark:border-green-900 rounded-lg p-4 mb-6 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-2 h-full bg-green-500"></div>
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <h5 class="text-sm font-bold text-gray-900 dark:text-white">العقد الحالي (النشط)</h5>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800">ACTIVE</span>
                            </div>
                            <p class="text-xs text-gray-500">من {{ $activeContract->start_date->format('Y-m-d') }} إلى {{ $activeContract->end_date->format('Y-m-d') }}</p>
                        </div>
                        <div class="text-left">
                            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($activeContract->basic_salary, 2) }} <span class="text-xs font-normal text-gray-500">ر.س</span></p>
                            <p class="text-xs text-gray-500">أساسي</p>
                        </div>
                    </div>
                    {{-- Items --}}
                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 grid grid-cols-2 gap-4">
                         @foreach($activeContract->contractItems as $item)
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-600 dark:text-gray-400">{{ $item->name }}</span>
                                <span class="font-medium {{ $item->type === 'allowance' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $item->type === 'allowance' ? '+' : '-' }}{{ number_format($item->amount, 0) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- History --}}
            <div class="space-y-4">
                @foreach($contractHistory as $contract)
                    <div class="bg-gray-50 dark:bg-gray-700/30 border border-gray-200 dark:border-gray-700 rounded-lg p-4 opacity-75 hover:opacity-100 transition-opacity">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">عقد سابق</p>
                                <p class="text-xs text-gray-500">{{ $contract->start_date->format('Y-m-d') }} - {{ $contract->end_date->format('Y-m-d') }}</p>
                            </div>
                            <div class="text-left">
                                <p class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ number_format($contract->basic_salary, 2) }} ر.س</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-200 text-gray-800">
                                    {{ $contract->status }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 3. Payrolls Tab --}}
        <div x-show="activeTab === 'payrolls'" style="display: none;">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الشهر</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الأساسي</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الإضافات</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الخصومات</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الصافي</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الحالة</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">عرض</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($payrolls as $record)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $record->batch->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ number_format($record->basic_salary, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                    +{{ number_format($record->total_additions, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                    -{{ number_format($record->total_deductions, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                    {{ number_format($record->net_salary, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $record->batch->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $record->batch->status === 'paid' ? 'مدفوع' : 'معلق' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('payroll.payslip.show', $record->id) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        عرض القسيمة
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">لا توجد سجلات رواتب سابقة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 4. Loans Tab --}}
        <div x-show="activeTab === 'loans'" style="display: none;">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">إجمالي السلف</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($loans->sum('amount'), 2) }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">المسدد</p>
                    <p class="text-xl font-bold text-green-600">{{ number_format($loans->sum('paid_amount'), 2) }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">المتبقي</p>
                    <p class="text-xl font-bold text-red-600">{{ number_format($loans->sum('remaining_amount'), 2) }}</p>
                </div>
            </div>

            <div class="flex justify-end mb-4">
                <a href="{{ route('payroll.loans.index') }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                    طلب سلفة جديدة
                </a>
            </div>

            <div class="space-y-4">
                @forelse($loans as $loan)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h5 class="text-sm font-bold text-gray-900 dark:text-white">سلفة #{{ $loan->id }}</h5>
                                <p class="text-xs text-gray-500">{{ $loan->created_at->format('Y-m-d') }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded text-xs font-medium {{ $loan->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $loan->status }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-sm mb-2">
                            <span>القيمة: {{ number_format($loan->amount, 2) }}</span>
                            <span>القسط: {{ number_format($loan->monthly_installment, 2) }}</span>
                            <span class="font-bold text-red-600">المتبقي: {{ number_format($loan->remaining_amount, 2) }}</span>
                        </div>
                        {{-- Progress Bar --}}
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                            @php $percent = $loan->amount > 0 ? ($loan->paid_amount / $loan->amount) * 100 : 0; @endphp
                            <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 text-sm py-4">لا توجد سلف مسجلة.</p>
                @endforelse
            </div>
        </div>

        {{-- 5. Variations Tab --}}
        <div x-show="activeTab === 'variations'" style="display: none;">
            <p class="text-sm text-gray-500 mb-4">آخر 10 خصومات أو إضافات متغيرة تم تسجيلها في الرواتب.</p>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">التاريخ</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">النوع</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المبلغ</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">البيان</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($variations as $item)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $item->created_at->format('Y-m-d') }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 py-0.5 rounded text-xs {{ $item->type === 'allowance' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $item->type === 'allowance' ? 'إضافة' : 'خصم' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-bold {{ $item->type === 'allowance' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($item->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $item->name }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">لا توجد سجلات حديثة.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 6. Bank Info Tab --}}
        <div x-show="activeTab === 'bank'" style="display: none;">
            <div class="max-w-xl">
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                هذه المعلومات حساسة ومحمية. لتعديلها يرجى التواصل مع المدير المالي.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">اسم البنك</label>
                        <input type="text" disabled value="{{ $activeContract->bank_name ?? 'غير مسجل' }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">رقم الآيبان (IBAN)</label>
                        <input type="text" disabled value="{{ $activeContract->iban ?? 'غير مسجل' }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 font-mono" dir="ltr">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

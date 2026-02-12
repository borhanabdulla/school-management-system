<div class="min-h-screen bg-gradient-to-br from-indigo-950 via-purple-950 to-indigo-950 p-6">
    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">لوحة المالك</h1>
            <p class="text-slate-400">نظرة شاملة على التدفقات المالية والمستحقات</p>
        </div>

        {{-- Period Selector --}}
        <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10 mb-8">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                <span class="text-slate-300 font-medium">الفترة:</span>
                
                <div class="flex gap-2">
                    <button 
                        wire:click="$set('period', 'today')"
                        class="px-4 py-2 rounded-lg transition-all {{ $period === 'today' ? 'bg-emerald-500 text-white' : 'bg-white/10 text-slate-300 hover:bg-white/20' }}"
                    >
                        اليوم
                    </button>
                    <button 
                        wire:click="$set('period', 'month')"
                        class="px-4 py-2 rounded-lg transition-all {{ $period === 'month' ? 'bg-emerald-500 text-white' : 'bg-white/10 text-slate-300 hover:bg-white/20' }}"
                    >
                        الشهر
                    </button>
                    <button 
                        wire:click="$set('period', 'year')"
                        class="px-4 py-2 rounded-lg transition-all {{ $period === 'year' ? 'bg-emerald-500 text-white' : 'bg-white/10 text-slate-300 hover:bg-white/20' }}"
                    >
                        السنة
                    </button>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-slate-300 font-medium text-sm">السنة الدراسية:</span>
                    <select wire:model.live="selectedYearId" class="bg-white/10 border border-white/10 text-slate-300 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block p-2">
                        <option value="">كل السنوات</option>
                        @foreach($years as $year)
                            <option value="{{ $year['id'] }}">{{ $year['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <button wire:click="exportCashFlow" class="px-4 py-2 bg-white/10 rounded-lg backdrop-blur-sm border border-white/10 text-sm font-medium hover:bg-white/20 transition-colors flex items-center gap-2 text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        تصدير Ledger
                    </button>
                    <button wire:click="exportReceivables" class="px-4 py-2 bg-white/10 rounded-lg backdrop-blur-sm border border-white/10 text-sm font-medium hover:bg-white/20 transition-colors flex items-center gap-2 text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        تصدير المستحقات
                    </button>
                </div>
            </div>
                </div>
            </div>
        </div>

        {{-- Cash Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            {{-- Total In --}}
            <div class="bg-gradient-to-br from-emerald-500/20 to-emerald-600/10 backdrop-blur-xl rounded-2xl p-6 border border-emerald-500/30">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-emerald-300 font-medium">إجمالي الدخل (النقد المستلم)</span>
                    <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        </svg>
                    </div>
                </div>
                <div class="text-4xl font-bold text-white mb-2" dir="ltr">
                    {{ number_format($dashboardData['cash']['total_in'] ?? 0, 2) }}
                    <span class="text-lg text-emerald-300">ر.س</span>
                </div>
                <p class="text-emerald-300/70 text-sm">{{ $dashboardData['period']['label'] ?? 'هذا الشهر' }}</p>
            </div>

            {{-- Total Out --}}
            <div class="bg-gradient-to-br from-rose-500/20 to-rose-600/10 backdrop-blur-xl rounded-2xl p-6 border border-rose-500/30">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-rose-300 font-medium">إجمالي المصروفات</span>
                    <div class="w-12 h-12 bg-rose-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                        </svg>
                    </div>
                </div>
                <div class="text-4xl font-bold text-white mb-2" dir="ltr">
                    {{ number_format($dashboardData['cash']['total_out'] ?? 0, 2) }}
                    <span class="text-lg text-rose-300">ر.س</span>
                </div>
                <p class="text-rose-300/70 text-sm">{{ $dashboardData['period']['label'] ?? 'هذا الشهر' }}</p>
            </div>

            {{-- Net Cash --}}
            @php $netCash = ($dashboardData['cash']['net_cash'] ?? 0); @endphp
            <div class="bg-gradient-to-br from-blue-500/20 to-indigo-600/10 backdrop-blur-xl rounded-2xl p-6 border border-blue-500/30">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-blue-300 font-medium">النقد المتاح (الصندوق)</span>
                    <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-4xl font-bold mb-2 {{ $netCash >= 0 ? 'text-emerald-400' : 'text-rose-400' }}" dir="ltr">
                    {{ number_format($netCash, 2) }}
                    <span class="text-lg {{ $netCash >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">ر.س</span>
                </div>
                <p class="text-blue-300/70 text-sm">{{ $dashboardData['period']['label'] ?? 'هذا الشهر' }}</p>
            </div>
        </div>

        {{-- Receivables Card --}}
        <div class="bg-gradient-to-br from-amber-500/20 to-orange-600/10 backdrop-blur-xl rounded-2xl p-6 border border-amber-500/30 mb-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xl font-bold text-white">المستحقات (غير محصلة)</h3>
                    <p class="text-amber-300/70 text-sm">مبالغ الفواتير التي لم تُسدد بعد</p>
                </div>
                <div class="w-16 h-16 bg-amber-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- All Years (Primary) -->
                 <div class="text-center md:text-right md:border-l md:border-white/10 md:pl-8">
                    <p class="text-amber-300/70 text-sm mb-1">إجمالي المستحقات (تراكمي لجميع السنوات)</p>
                    <p class="text-4xl font-bold text-white mb-2" dir="ltr">
                        {{ number_format($dashboardData['receivables_all']['total_outstanding'] ?? 0, 2) }}
                        <span class="text-lg text-amber-500/50">ر.س</span>
                    </p>
                    <div class="flex flex-wrap gap-4 text-xs text-slate-400">
                        <span>إجمالي الفواتير: {{ number_format($dashboardData['receivables_all']['total_billed'] ?? 0, 0) }}</span>
                        <span>•</span>
                        <span>إجمالي المحصّل: {{ number_format($dashboardData['receivables_all']['total_collected'] ?? 0, 0) }}</span>
                    </div>
                </div>

                <!-- Specific Year (Secondary) -->
                <div class="text-center md:text-right">
                    @if($dashboardData['receivables_year'])
                        <p class="text-amber-300/70 text-sm mb-1">
                            مستحقات ({{ collect($years)->firstWhere('id', $selectedYearId)['name'] ?? 'السنة المحددة' }})
                        </p>
                        <p class="text-3xl font-bold text-amber-400 mb-2" dir="ltr">
                            {{ number_format($dashboardData['receivables_year']['total_outstanding'] ?? 0, 2) }}
                            <span class="text-base text-amber-500/50">ر.س</span>
                        </p>
                        <div class="flex flex-wrap gap-4 text-xs text-slate-400">
                            <span>فواتير السنة: {{ number_format($dashboardData['receivables_year']['total_billed'] ?? 0, 0) }}</span>
                            <span>•</span>
                            <span>تحصيل السنة: {{ number_format($dashboardData['receivables_year']['total_collected'] ?? 0, 0) }}</span>
                        </div>
                    @else
                        <div class="h-full flex flex-col items-center justify-center text-slate-500 text-sm italic border-2 border-dashed border-white/5 rounded-xl p-4">
                            <span class="mb-1">لم يتم تحديد سنة</span>
                            <span class="text-xs">اختر سنة من القائمة بالأعلى لعرض أرقامها المنفصلة</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-6 border border-white/10">
            <h3 class="text-xl font-bold text-white mb-4">آخر الحركات</h3>
            
            @if(count($dashboardData['recent_transactions'] ?? []) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-white/10">
                                <th class="text-right py-3 px-4 text-slate-400 font-medium text-sm">التاريخ</th>
                                <th class="text-right py-3 px-4 text-slate-400 font-medium text-sm">النوع</th>
                                <th class="text-right py-3 px-4 text-slate-400 font-medium text-sm">المبلغ</th>
                                <th class="text-right py-3 px-4 text-slate-400 font-medium text-sm">ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dashboardData['recent_transactions'] as $tx)
                                <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                                    <td class="py-3 px-4 text-slate-300 text-sm">{{ $tx['date'] }}</td>
                                    <td class="py-3 px-4">
                                        @if($tx['direction'] === 'in')
                                            <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 rounded text-xs">دخل</span>
                                        @else
                                            <span class="px-2 py-1 bg-rose-500/20 text-rose-400 rounded text-xs">خرج</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-white font-medium" dir="ltr">{{ number_format($tx['amount'], 2) }}</td>
                                    <td class="py-3 px-4 text-slate-400 text-sm">{{ $tx['notes'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-slate-500">لا توجد حركات مسجلة بعد</p>
                </div>
            @endif
        </div>

        {{-- Info Note --}}
        <div class="mt-8 bg-blue-500/10 border border-blue-500/30 rounded-xl p-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-blue-200 font-medium">ملاحظة</p>
                <p class="text-blue-300/70 text-sm">
                    <strong>النقد المتاح</strong> = السيولة الحالية (Ledger) حسب الفترة الزمنية المحددة (فوق). <br>
                    <strong>المستحقات</strong> = الديون التي لم يتم تحصيلها. يمكنك تصفيتها حسب السنة الدراسية باستخدام القائمة.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="py-8">
    <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">تقرير الحضور الشهري</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">مصفوفة ذكية لمتابعة حضور وغياب الطلاب</p>
            </div>
            
            {{-- Filters --}}
            <div class="flex flex-wrap items-center gap-3">
                {{-- Section Filter --}}
                <select wire:model.live="classSectionId" 
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @foreach($this->availableSections as $section)
                        <option value="{{ $section->id }}">{{ $section->full_name }}</option>
                    @endforeach
                </select>
                
                {{-- Month Filter --}}
                <select wire:model.live="month" 
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @foreach($this->months as $num => $name)
                        <option value="{{ $num }}">{{ $name }}</option>
                    @endforeach
                </select>
                
                {{-- Year Filter --}}
                <select wire:model.live="year" 
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @foreach($this->years as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
                
                {{-- Print Button --}}
                <button onclick="window.print()" 
                        class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition text-sm font-medium print:hidden">
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    طباعة
                </button>
            </div>
        </div>

        {{-- Summary Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 print:hidden">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->reportData['students']->count() }}</div>
                <div class="text-xs text-gray-500">عدد الطلاب</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 text-center">
                <div class="text-2xl font-bold text-indigo-600">{{ $this->schoolDaysCount }}</div>
                <div class="text-xs text-gray-500">أيام الدراسة</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 text-center">
                <div class="text-2xl font-bold text-red-600">
                    {{ collect($this->reportData['stats'])->sum('absences') }}
                </div>
                <div class="text-xs text-gray-500">إجمالي الغياب</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 text-center">
                <div class="text-2xl font-bold text-yellow-600">
                    {{ collect($this->reportData['stats'])->sum('lates') }}
                </div>
                <div class="text-xs text-gray-500">إجمالي التأخير</div>
            </div>
        </div>

        {{-- Matrix Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-center border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50">
                            {{-- Sticky Student Column --}}
                            <th class="sticky right-0 z-10 bg-gray-100 dark:bg-gray-700 p-3 text-right font-bold text-gray-700 dark:text-gray-200 border-l border-gray-200 dark:border-gray-600 min-w-[180px]">
                                الطالب
                            </th>
                            
                            {{-- Day Headers --}}
                            @foreach($this->reportData['days'] as $day)
                                <th class="p-2 min-w-[40px] border-l border-gray-200 dark:border-gray-600 last:border-l-0 
                                    {{ $day['is_holiday'] ? 'bg-gray-200 dark:bg-gray-600' : '' }}">
                                    <div class="flex flex-col items-center">
                                        <span class="text-[10px] text-gray-500 dark:text-gray-400">{{ $day['day_short'] }}</span>
                                        <span class="text-xs font-bold text-gray-800 dark:text-white">{{ $day['day_num'] }}</span>
                                    </div>
                                </th>
                            @endforeach
                            
                            {{-- Stats Column --}}
                            <th class="p-3 bg-red-50 dark:bg-red-900/20 font-bold text-red-700 dark:text-red-300 min-w-[60px] border-r border-gray-200 dark:border-gray-600">
                                غياب
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($this->reportData['students'] as $student)
                            @php
                                $studentStats = $this->reportData['stats'][$student->id] ?? ['absences' => 0, 'lates' => 0];
                                $studentMatrix = $this->reportData['matrix'][$student->id] ?? [];
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                {{-- Sticky Student Name --}}
                                <td class="sticky right-0 z-10 bg-white dark:bg-gray-800 p-3 text-right font-medium text-gray-900 dark:text-white border-l border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center gap-2">
                                        <span>{{ $student->full_name_ar ?: $student->full_name_en ?: $student->admission_number }}</span>
                                        <span class="text-[10px] text-gray-400">{{ $student->admission_number }}</span>
                                    </div>
                                </td>
                                
                                {{-- Day Cells --}}
                                @foreach($this->reportData['days'] as $day)
                                    @php
                                        $cell = $studentMatrix[$day['date']] ?? ['status' => 'no_data', 'delay' => 0];
                                        $cellClass = match($cell['status']) {
                                            'absent', 'escaped' => 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300',
                                            'late' => 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300',
                                            'excused' => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300',
                                            'holiday' => 'bg-gray-200 dark:bg-gray-600',
                                            'no_data' => 'bg-gray-50 dark:bg-gray-800 text-gray-300',
                                            default => '', // present - empty
                                        };
                                        $cellText = match($cell['status']) {
                                            'absent', 'escaped' => 'غ',
                                            'late' => 'L',
                                            'excused' => 'ع',
                                            'no_data' => '?',
                                            default => '',
                                        };
                                    @endphp
                                    <td class="p-1 border-l border-gray-100 dark:border-gray-700 last:border-l-0 {{ $cellClass }}"
                                        @if($cell['status'] === 'late' && $cell['delay'] > 0)
                                            title="تأخر {{ $cell['delay'] }} دقيقة"
                                        @endif>
                                        <span class="text-xs font-bold">{{ $cellText }}</span>
                                    </td>
                                @endforeach
                                
                                {{-- Absence Count --}}
                                <td class="p-2 font-bold border-r border-gray-200 dark:border-gray-600
                                    {{ $studentStats['absences'] > 3 ? 'text-red-600 bg-red-50 dark:bg-red-900/30' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ $studentStats['absences'] }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($this->reportData['days']) + 2 }}" class="p-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p>لا يوجد طلاب في هذه الشعبة</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-400 print:hidden">
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded bg-red-100 dark:bg-red-900/40"></span>
                <span>غائب (غ)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded bg-yellow-100 dark:bg-yellow-900/40"></span>
                <span>متأخر (L)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded bg-blue-100 dark:bg-blue-900/40"></span>
                <span>معذور (ع)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded bg-gray-200 dark:bg-gray-600"></span>
                <span>عطلة</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded bg-gray-50 dark:bg-gray-800 border border-gray-200"></span>
                <span>لم يُرصد (?)</span>
        </div>
    </div>

    {{-- Print Styles --}}
    <style>
        @media print {
            body { background: white !important; }
            .print\:hidden { display: none !important; }
            table { font-size: 10px !important; }
            th, td { padding: 4px !important; }
            .bg-red-100, .bg-yellow-100, .bg-blue-100 { 
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</div>

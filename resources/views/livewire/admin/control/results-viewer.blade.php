<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="min-h-screen" dir="rtl">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 mb-8">
                <div>
                    <h1 class="text-3xl font-black bg-gradient-to-l from-purple-600 via-pink-600 to-red-500 bg-clip-text text-transparent">
                        النتائج النهائية
                    </h1>
                    @if($this->session)
                        <p class="text-gray-600 dark:text-gray-400 mt-2">
                            {{ $this->session->name }} | {{ $this->session->academicYear->name }} - {{ $this->session->term->name }}
                        </p>
                    @endif
                </div>

                <div class="flex gap-2">
                    <button onclick="window.print()"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-xl transition-colors">
                        🖨️ طباعة
                    </button>
                    <a href="{{ route('control.dashboard') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-xl transition-colors">
                        عودة
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Subject Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">المادة</label>
                        <select wire:model.live="courseOfferingId"
                                class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">جميع المواد</option>
                            @foreach($this->subjects as $subject)
                                <option value="{{ $subject->id }}">
                                    {{ $subject->subject->name }} - {{ $subject->classSection->grade->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">الحالة</label>
                        <select wire:model.live="statusFilter"
                                class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="all">الكل</option>
                            <option value="pass">ناجح</option>
                            <option value="fail">راسب</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            @if($this->stats)
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 text-center border border-gray-200 dark:border-gray-700">
                        <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ $this->stats['total'] }}</div>
                        <div class="text-xs text-gray-500">إجمالي النتائج</div>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/30 rounded-xl p-4 text-center border border-green-200 dark:border-green-700">
                        <div class="text-3xl font-bold text-green-600">{{ $this->stats['passed'] }}</div>
                        <div class="text-xs text-green-600">ناجح</div>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/30 rounded-xl p-4 text-center border border-red-200 dark:border-red-700">
                        <div class="text-3xl font-bold text-red-600">{{ $this->stats['failed'] }}</div>
                        <div class="text-xs text-red-600">راسب</div>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/30 rounded-xl p-4 text-center border border-blue-200 dark:border-blue-700">
                        <div class="text-3xl font-bold text-blue-600">{{ $this->stats['pass_rate'] }}%</div>
                        <div class="text-xs text-blue-600">نسبة النجاح</div>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-900/30 rounded-xl p-4 text-center border border-purple-200 dark:border-purple-700">
                        <div class="text-3xl font-bold text-purple-600">{{ $this->stats['average'] }}</div>
                        <div class="text-xs text-purple-600">المتوسط</div>
                    </div>
                </div>
            @endif

            <!-- Results Table -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden print:border-black">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">#</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الطالب</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">المادة</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">أعمال السنة</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الاختبار</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">المجموع</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">التقدير</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($this->results as $index => $result)
                            <tr class="{{ $result->status === 'fail' ? 'bg-red-50 dark:bg-red-900/10' : '' }}">
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                    <div class="flex items-center gap-2">
                                        {{ $result->student->full_name ?? 'N/A' }}
                                        @if($result->student->seatings->firstWhere('exam_session_id', $this->sessionId)?->is_withheld)
                                            <span class="px-1.5 py-0.5 bg-red-100 text-red-600 text-[10px] font-bold rounded border border-red-200">
                                                محجوب
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $result->courseOffering->subject->name ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">
                                    {{ $result->coursework_score }}
                                </td>
                                <td class="px-4 py-3 text-sm text-center text-gray-900 dark:text-white">
                                    {{ $result->final_exam_score }}
                                </td>
                                <td class="px-4 py-3 text-sm text-center font-bold text-gray-900 dark:text-white">
                                    {{ $result->total_score }}
                                </td>
                                <td class="px-4 py-3 text-sm text-center">
                                    <span class="px-2 py-1 rounded-lg text-xs font-medium
                                        {{ $result->grade_label === 'ممتاز' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $result->grade_label === 'جيد جداً' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $result->grade_label === 'جيد' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                        {{ $result->grade_label === 'مقبول' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ in_array($result->grade_label, ['ضعيف', 'راسب']) ? 'bg-red-100 text-red-800' : '' }}
                                    ">
                                        {{ $result->grade_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-center">
                                    @if($result->status === 'pass')
                                        <span class="text-green-600 font-bold">✓ ناجح</span>
                                    @elseif($result->status === 'fail')
                                        <span class="text-red-600 font-bold">✗ راسب</span>
                                    @elseif($result->status === 'absent')
                                        <span class="text-gray-500">غائب</span>
                                    @else
                                        <span class="text-yellow-600">{{ $result->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                    لا توجد نتائج بعد. قم بمعالجة النتائج أولاً.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Print Styles -->
    <style>
        @media print {
            body * { visibility: hidden; }
            .min-h-screen, .min-h-screen * { visibility: visible; }
            .min-h-screen { position: absolute; left: 0; top: 0; width: 100%; }
            button, a, select, .no-print { display: none !important; }
            .rounded-2xl { border-radius: 0 !important; }
            table { font-size: 12px; }
        }
    </style>
</div>

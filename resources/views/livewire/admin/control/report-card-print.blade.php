<div class="w-full bg-white">
    @foreach($students as $student)
        @php
            $session = $this->session;
            $seating = $student->seatings->firstWhere('exam_session_id', $session->id);
            $isWithheld = $seating?->is_withheld ?? false;
            $withholdReason = $seating?->withhold_reason;
            $report = $reports[$student->id] ?? null;
            $subjects = $report['subjects'] ?? [];
            $stats = $report['totals'] ?? [
                'total_score' => 0,
                'max_score' => 0,
                'percentage' => 0,
                'grade_label' => '-',
            ];
            $meta = $report['meta'] ?? [];
            $stats['rank'] = $ranks[$student->id] ?? '-';
        @endphp

        <div class="relative w-full min-h-screen p-8 mb-8 bg-white print:mb-0 print:break-after-page">
            @if($isWithheld)
                <div class="flex flex-col items-center justify-center h-[800px] border-8 border-double border-red-600 rounded-3xl p-12 text-center bg-red-50">
                    <div class="text-9xl mb-8 text-red-600">🚫</div>
                    <h2 class="text-6xl font-black text-red-800 mb-6">النتيجة محجوبة</h2>
                    <p class="text-2xl text-red-700 mb-12 max-w-2xl leading-relaxed">
                        {{ $withholdReason ?: 'عذراً، لقد تم حجب نتيجتك لهذا الفصل. يرجى مراجعة إدارة المدرسة أو الشؤون المالية للمزيد من التفاصيل.' }}
                    </p>
                    <div class="space-y-2">
                        <div class="text-xl font-bold text-gray-800">{{ $student->full_name_ar }}</div>
                        <div class="text-lg text-red-500">رقم الجلوس: {{ $seating?->seat_number }}</div>
                    </div>
                </div>
            @else
                <div class="absolute inset-0 m-4 border-4 border-double border-gray-800 pointer-events-none"></div>

                <div class="relative z-10 mb-8 text-center">
                    <div class="flex items-center justify-between px-12 pt-8">
                        <div class="text-center">
                            <h2 class="text-xl font-bold text-gray-800">المملكة العربية السعودية</h2>
                            <h3 class="text-lg text-gray-600">وزارة التعليم</h3>
                            <h3 class="text-lg text-gray-600">نظام إدارة المدارس الذكي</h3>
                        </div>
                        <div class="w-24 h-24">
                            <svg class="w-full h-full text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <div class="text-center">
                            <h2 class="text-xl font-bold text-gray-800">إشعار بنتيجة الطالب</h2>
                            <h3 class="text-lg text-gray-600">{{ $session->name }}</h3>
                            <h3 class="text-lg text-gray-600">العام الدراسي {{ $session->academicYear->name }}</h3>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 mb-8 px-12">
                    <table class="w-full border-collapse">
                        <tr>
                            <td class="py-2 font-bold text-gray-700 w-32">اسم الطالب:</td>
                            <td class="py-2 text-gray-900 border-b border-gray-300">{{ $student->full_name_ar }}</td>
                            <td class="py-2 font-bold text-gray-700 w-32 text-right pr-4">رقم الجلوس:</td>
                            <td class="py-2 text-gray-900 border-b border-gray-300 w-32 text-center">
                                {{ $seating?->seat_number ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 font-bold text-gray-700">الصف:</td>
                            <td class="py-2 text-gray-900 border-b border-gray-300">{{ $meta['student']['grade'] ?? ($student->currentClassSection?->grade?->name ?? '-') }}</td>
                            <td class="py-2 font-bold text-gray-700 text-right pr-4">الفصل:</td>
                            <td class="py-2 text-gray-900 border-b border-gray-300 text-center">{{ $meta['student']['class_section'] ?? ($student->currentClassSection?->name ?? '-') }}</td>
                        </tr>
                    </table>
                </div>

                <div class="relative z-10 px-12 mb-8">
                    <table class="w-full border border-gray-800">
                        <thead>
                            <tr class="bg-gray-100 text-gray-800">
                                <th class="border border-gray-800 py-3 px-4 text-right w-1/3">المادة الدراسية</th>
                                <th class="border border-gray-800 py-3 px-4 text-center">الدرجة العظمى</th>
                                <th class="border border-gray-800 py-3 px-4 text-center">درجة النجاح</th>
                                <th class="border border-gray-800 py-3 px-4 text-center">درجة الطالب</th>
                                <th class="border border-gray-800 py-3 px-4 text-center">التقدير</th>
                                <th class="border border-gray-800 py-3 px-4 text-center">الملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subjects as $subject)
                                <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                                    <td class="border border-gray-800 py-2 px-4 font-medium">{{ $subject['subject']['name'] }}</td>
                                    <td class="border border-gray-800 py-2 px-4 text-center">{{ number_format($subject['max_score'], 2) }}</td>
                                    <td class="border border-gray-800 py-2 px-4 text-center">{{ $subject['pass_score'] ?? '—' }}</td>
                                    <td class="border border-gray-800 py-2 px-4 text-center font-bold {{ $subject['is_passed'] ? 'text-gray-900' : 'text-red-600' }}">
                                        {{ number_format($subject['total_score'], 2) }}
                                    </td>
                                    <td class="border border-gray-800 py-2 px-4 text-center">{{ $subject['grade_label'] }}</td>
                                    <td class="border border-gray-800 py-2 px-4 text-center text-sm text-gray-500">
                                        {{ $subject['is_passed'] ? '' : 'إعادة' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-center text-gray-500">لا توجد نتائج متوفرة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-200 font-bold">
                                <td class="border border-gray-800 py-3 px-4 text-right">المجموع الكلي</td>
                                <td class="border border-gray-800 py-3 px-4 text-center">{{ number_format($stats['max_score'] ?? 0, 2) }}</td>
                                <td class="border border-gray-800 py-3 px-4 text-center">-</td>
                                <td class="border border-gray-800 py-3 px-4 text-center">{{ number_format($stats['total_score'] ?? 0, 2) }}</td>
                                <td class="border border-gray-800 py-3 px-4 text-center">{{ $stats['grade_label'] }}</td>
                                <td class="border border-gray-800 py-3 px-4 text-center">{{ number_format($stats['percentage'] ?? 0, 1) }}%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="relative z-10 px-12 mt-16">
                    <div class="flex justify-between items-end">
                        <div class="text-center w-1/3">
                            <p class="mb-4 font-bold text-gray-800">مدخل البيانات</p>
                            <div class="h-16 border-b border-dashed border-gray-400"></div>
                        </div>
                        <div class="text-center w-1/3">
                            <p class="mb-4 font-bold text-gray-800">وكيل الشؤون التعليمية</p>
                            <div class="h-16 border-b-b border-dashed border-gray-400"></div>
                        </div>
                        <div class="text-center w-1/3">
                            <p class="mb-4 font-bold text-gray-800">مدير المدرسة</p>
                            <div class="h-16 border-b border-dashed border-gray-400"></div>
                            <p class="mt-2 text-sm text-gray-600">ختم المدرسة</p>
                        </div>
                    </div>
                </div>

                <div class="absolute bottom-8 left-12 text-xs text-gray-400">
                    تاريخ الطباعة: {{ now()->format('Y-m-d H:i') }}
                </div>
            @endif
        </div>
    @endforeach
</div>

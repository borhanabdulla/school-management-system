<div class="min-h-screen bg-gray-100 p-8 font-sans" dir="rtl">
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden print:shadow-none print:w-full print:max-w-none">
        <!-- Header -->
        <div class="bg-blue-900 text-white p-6 text-center print:bg-white print:text-black print:border-b-2 print:border-black">
            <div class="flex justify-between items-center">
                <div class="text-right">
                    <h2 class="text-xl font-bold">{{ $schoolName }}</h2>
                    <p class="text-sm opacity-80">العام الدراسي: {{ $academicYearName }}</p>
                </div>
                <div class="text-center">
                    <h1 class="text-3xl font-bold mb-2">شهادة النتيجة السنوية</h1>
                </div>
                <div class="text-left">
                    <p class="text-sm">تاريخ الإصدار: {{ now()->format('Y-m-d') }}</p>
                </div>
            </div>
        </div>

        <!-- Student Info -->
        <div class="p-6 border-b border-gray-200 print:border-none">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-gray-600 text-sm">اسم الطالب</p>
                    <p class="text-xl font-bold text-gray-900">{{ $result->student->full_name_ar }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">الرقم الأكاديمي</p>
                    <p class="text-xl font-bold text-gray-900">{{ $result->student->admission_number }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">الصف</p>
                    <p class="text-xl font-bold text-gray-900">{{ $result->grade->name }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">الشعبة</p>
                    <p class="text-xl font-bold text-gray-900">{{ $result->student->currentClassSection->name ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Results Table -->
        <div class="p-6">
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-50 print:bg-gray-100">
                        <th class="border border-gray-300 px-4 py-2 text-right">البيان</th>
                        <th class="border border-gray-300 px-4 py-2 text-center">الدرجة</th>
                        <th class="border border-gray-300 px-4 py-2 text-center">العظمى</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-gray-300 px-4 py-3 font-medium">مجموع الترم الأول</td>
                        <td class="border border-gray-300 px-4 py-3 text-center">{{ $result->term1_total }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-center">{{ $result->term1_max }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-300 px-4 py-3 font-medium">مجموع الترم الثاني</td>
                        <td class="border border-gray-300 px-4 py-3 text-center">{{ $result->term2_total }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-center">{{ $result->term2_max }}</td>
                    </tr>
                    <tr class="bg-gray-100 font-bold print:bg-gray-200">
                        <td class="border border-gray-300 px-4 py-3">المجموع الكلي</td>
                        <td class="border border-gray-300 px-4 py-3 text-center text-lg">{{ $result->annual_total }}</td>
                        <td class="border border-gray-300 px-4 py-3 text-center text-lg">{{ $result->annual_max }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-6 flex justify-between items-center bg-gray-50 p-4 rounded-lg border border-gray-200 print:bg-white print:border-black">
                <div>
                    <span class="text-gray-600 font-medium ml-2">النسبة المئوية:</span>
                    <span class="text-xl font-bold">{{ number_format($result->percentage, 1) }}%</span>
                </div>
                <div>
                    <span class="text-gray-600 font-medium ml-2">الترتيب:</span>
                    <span class="text-xl font-bold">{{ $result->rank ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-600 font-medium ml-2">النتيجة النهائية:</span>
                    <span class="text-xl font-bold 
                        @if($result->decision === 'pass') text-green-600 
                        @elseif($result->decision === 'fail') text-red-600 
                        @elseif($result->decision === 'conditional') text-orange-600 
                        @endif print:text-black">
                        @if($result->decision === 'pass') ناجح
                        @elseif($result->decision === 'fail') راسب
                        @elseif($result->decision === 'conditional') مُكمِّل
                        @else قيد الانتظار
                        @endif
                    </span>
                </div>
            </div>

            @if($result->failed_count > 0 && is_array($result->failed_subjects))
                <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg print:bg-white print:border-black">
                    <h3 class="font-bold text-red-800 mb-2 print:text-black">المواد التي رسب فيها الطالب:</h3>
                    <p class="text-red-700 print:text-black">
                        @foreach($result->failed_subjects as $subject)
                            {{ $subject['subject_name'] ?? 'مادة غير معروفة' }}
                            @if(!$loop->last)، @endif
                        @endforeach
                    </p>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="p-6 mt-8 flex justify-between text-center print:mt-16">
            <div>
                <p class="font-bold mb-16">المرشد الطلابي</p>
                <p>....................</p>
            </div>
            <div>
                <p class="font-bold mb-16">وكيل المدرسة</p>
                <p>....................</p>
            </div>
            <div>
                <p class="font-bold mb-16">مدير المدرسة</p>
                <p>....................</p>
            </div>
        </div>

        <!-- Print Button (Hidden when printing) -->
        <div class="p-6 bg-gray-50 border-t border-gray-200 text-center print:hidden">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition">
                🖨️ طباعة الشهادة
            </button>
        </div>
    </div>
</div>

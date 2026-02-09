<div class="max-w-5xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

    {{-- زر العودة --}}
    <div class="mb-6">
        <a href="{{ route('teacher.dashboard') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
            عودة للوحة التحكم
        </a>
    </div>

    {{-- بطاقة المعلومات --}}
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm mb-6 border-r-4 {{ $hasExistingRecords ? 'border-green-500' : 'border-indigo-600' }}">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">رصد الحضور</h2>
                    @if($hasExistingRecords)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            تم الرصد مسبقاً
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                            جديد
                        </span>
                    @endif
                </div>
                <p class="text-gray-500 dark:text-gray-400 text-sm flex items-center gap-2 flex-wrap">
                    <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ $timetable->courseOffering->subject->name }}</span>
                    <span class="text-gray-300">|</span>
                    <span>{{ $timetable->classSection->full_name }}</span>
                    <span class="text-gray-300">|</span>
                    <span>{{ $timetable->timeSlot->label }}</span>
                </p>
            </div>
            <div class="text-left bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
                <div class="text-2xl font-bold text-gray-800 dark:text-white">{{ now()->format('d/m') }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ now()->translatedFormat('l') }}</div>
            </div>
        </div>
    </div>

    @if($isHoliday)
        <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-xl text-center text-red-700 dark:text-red-300 font-bold border border-red-200 dark:border-red-800">
            اليوم إجازة رسمية، لا يمكن رصد الحضور.
        </div>
    @else
        {{-- شريط الإحصائيات السريعة --}}
        <div class="grid grid-cols-4 gap-3 mb-6">
            <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-3 text-center border border-green-100 dark:border-green-800">
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $this->presentCount }}</div>
                <div class="text-xs text-green-700 dark:text-green-300">حاضر</div>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-3 text-center border border-red-100 dark:border-red-800">
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $this->absentCount }}</div>
                <div class="text-xs text-red-700 dark:text-red-300">غائب</div>
            </div>
            <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-3 text-center border border-amber-100 dark:border-amber-800">
                <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $this->lateCount }}</div>
                <div class="text-xs text-amber-700 dark:text-amber-300">متأخر</div>
            </div>
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-3 text-center border border-blue-100 dark:border-blue-800">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $this->excusedCount }}</div>
                <div class="text-xs text-blue-700 dark:text-blue-300">معذور</div>
            </div>
        </div>

        {{-- أزرار الإجراءات السريعة --}}
        <div class="flex flex-wrap items-center gap-2 mb-4">
            <button wire:click="setAllPresent" 
                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                تحديد الكل حاضر
            </button>
            <button wire:click="resetAll" 
                    class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                إعادة تعيين
            </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-4 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-12">#</th>
                            <th class="px-4 py-4 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">الطالب</th>
                            <th class="px-4 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">الحالة</th>
                            <th class="px-4 py-4 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">ملاحظات / تأخير</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
    @foreach($students as $index => $student)
        @include('livewire.teacher.components.attendance-student-row', ['student' => $student, 'index' => $index])
    @endforeach
</tbody>
                </table>
            </div>
        </div>

        {{-- زر الحفظ --}}
        <div class="mt-6 flex justify-end sticky bottom-4 z-10">
            <div class="bg-white dark:bg-gray-800 p-2 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
                <button wire:click="save" wire:loading.attr="disabled" wire:loading.class="opacity-75 cursor-wait"
                        class="w-full md:w-auto text-lg py-3 px-8 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow transition-transform transform hover:-translate-y-0.5 disabled:opacity-50">
                    <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $hasExistingRecords ? 'تحديث الحضور' : 'حفظ الحضور' }}
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        جاري الحفظ...
                    </span>
                </button>
            </div>
        </div>
    @endif
</div>

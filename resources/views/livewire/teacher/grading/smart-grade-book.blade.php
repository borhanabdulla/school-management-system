<div class="space-y-6 p-4">
    <!-- Header -->
    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-6">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('grading.gradebooks') }}" class="text-gray-500 hover:text-purple-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                        </svg>
                    </a>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
                        {{ $courseOffering->subject->name }}
                    </h2>
                    <span class="bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 px-3 py-1 rounded-full text-sm font-medium">
                        {{ $courseOffering->classSection->name ?? 'شعبة' }}
                    </span>
                </div>
                <p class="text-gray-500 dark:text-gray-400 text-sm">
                    {{ $courseOffering->classSection->grade->name ?? '' }} • {{ $courseOffering->term->name ?? 'الفصل الدراسي' }}
                </p>
            </div>
            
            <div class="flex gap-3">
                @if($settings->allow_custom_categories && $this->canManageGradebookSettings)
                    <button wire:click="$set('showAddCategoryModal', true)" 
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium shadow transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        إضافة قسم
                    </button>
                @endif
                <button class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium shadow transition">
                    تصدير Excel
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-4 text-white shadow-lg">
            <div class="text-blue-100 text-sm">المتوسط</div>
            <div class="text-2xl font-bold">{{ $this->classStats['avg'] }}</div>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-4 text-white shadow-lg">
            <div class="text-green-100 text-sm">أعلى درجة</div>
            <div class="text-2xl font-bold">{{ $this->classStats['max'] }}</div>
        </div>
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-4 text-white shadow-lg">
            <div class="text-red-100 text-sm">أدنى درجة</div>
            <div class="text-2xl font-bold">{{ $this->classStats['min'] }}</div>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-4 text-white shadow-lg">
            <div class="text-purple-100 text-sm">الناجحون</div>
            <div class="text-2xl font-bold">{{ $this->classStats['passing'] }}/{{ $this->students->count() }}</div>
        </div>
    </div>

    <!-- Gradebook Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl bg-white dark:bg-gray-900">
                <table class="min-w-full border-collapse text-sm" style="direction: rtl;">
                    <thead>
                        <!-- Header Row 1: Months & Static Columns -->
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <!-- Row Index -->
                            <th rowspan="2" class="w-10 bg-gray-100 dark:bg-gray-800 border-b border-l border-gray-200 dark:border-gray-700 text-gray-500 font-bold text-center sticky right-0 z-30">
                                #
                            </th>
                            
                            <!-- Student Info (Sticky Glassmorphism) -->
                            <th rowspan="2" class="w-64 bg-white/95 dark:bg-gray-900/95 backdrop-blur-md border-b border-l border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 font-bold text-right px-4 sticky right-10 z-30 shadow-[4px_0_24px_rgba(0,0,0,0.02)]">
                                <div class="flex flex-col gap-1">
                                    <span class="text-base">الطالب</span>
                                    <span class="text-[10px] font-normal text-gray-400">الاسم - الرقم - الحالة</span>
                                </div>
                            </th>

                            <!-- Months -->
                            @foreach($this->months as $month)
                                <th colspan="{{ count($this->categories) + 1 }}" 
                                    class="relative group bg-white dark:bg-gray-800 border-b border-l border-gray-200 dark:border-gray-700 text-center py-2">
                                    <div class="flex items-center justify-center gap-2">
                                        <span class="text-purple-700 dark:text-purple-400 font-bold text-base">{{ $month->name }}</span>
                                        <!-- Add Category Trigger -->
                                        @if($settings->allow_custom_categories && $this->canManageGradebookSettings)
                                            <button wire:click="$set('showAddCategoryModal', true)" 
                                                    class="opacity-0 group-hover:opacity-100 transition-opacity p-1 hover:bg-purple-100 dark:hover:bg-purple-900 rounded-full text-purple-600"
                                                    title="إضافة بند جديد">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </th>
                            @endforeach

                            <!-- Grand Total -->
                            <th rowspan="2" class="w-24 bg-gray-50 dark:bg-gray-800 border-b border-r border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-bold text-center sticky left-0 z-30 shadow-[-4px_0_24px_rgba(0,0,0,0.02)]">
                                النتيجة
                            </th>
                        </tr>

                        <!-- Header Row 2: Categories -->
                        <tr class="bg-gray-50/50 dark:bg-gray-800/50">
                            @foreach($this->months as $month)
                                @foreach($this->categories as $cat)
                                    <th class="w-20 border-b border-l border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 font-medium text-xs text-center py-2">
                                        <div class="flex flex-col items-center gap-1">
                                            <span>{{ $cat['label'] ?? '' }}</span>
                                            <span class="text-[9px] bg-gray-100 dark:bg-gray-700 px-1.5 rounded-full">{{ $cat['max_score'] }}</span>
                                        </div>
                                    </th>
                                @endforeach
                                <th class="w-20 bg-purple-50/30 dark:bg-purple-900/10 border-b border-l border-gray-200 dark:border-gray-700 text-purple-700 dark:text-purple-400 font-bold text-xs text-center py-2">
                                    المجموع
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($this->students as $index => $student)
                            @php
                                $grandTotal = $this->getStudentGrandTotal($student->id);
                                $gradeInfo = $this->getGradeInfo($grandTotal);
                                $isPassing = $gradeInfo['is_passing'];
                                $absenceWarning = $this->getAbsenceWarning($student->id);
                            @endphp
                            <tr class="group hover:bg-blue-50/50 dark:hover:bg-blue-900/20 transition-colors h-12">
                                <!-- 1. Row Index -->
                                <td class="bg-gray-50 dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 text-center font-medium text-gray-400 text-xs sticky right-0 z-20 group-hover:bg-blue-50/50 dark:group-hover:bg-blue-900/20">
                                    {{ $index + 1 }}
                                </td>

                                <!-- 2. Student Info (Sticky) -->
                                <td class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-md border-l border-gray-200 dark:border-gray-700 px-4 py-2 sticky right-10 z-20 shadow-[4px_0_24px_rgba(0,0,0,0.02)] group-hover:bg-blue-50/80 dark:group-hover:bg-blue-900/20">
                                    <div class="flex flex-col justify-center h-full">
                                        <div class="font-bold text-gray-800 dark:text-gray-200 text-sm leading-tight">
                                            {{ $student->full_name_ar ?? ($student->first_name_ar . ' ' . $student->family_name_ar) }}
                                        </div>
                                        <div class="flex justify-between items-center mt-1">
                                            <span class="text-[10px] text-gray-400 font-mono">{{ $student->admission_number }}</span>
                                            @if($absenceWarning['warning'])
                                                <div class="flex items-center gap-1 text-red-500" title="{{ $absenceWarning['message'] }}">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                    </svg>
                                                    <span class="text-[9px] font-bold">تجاوز الغياب</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- 3. Grade Grid -->
                                @foreach($this->months as $month)
                                    @foreach($this->categories as $cat)
                                        <td class="border-l border-gray-100 dark:border-gray-800 p-0 text-center relative w-20">
                                            <input type="number" 
                                                   step="0.5"
                                                   min="0"
                                                   max="{{ $cat['max_score'] }}"
                                                   wire:change="updateGrade({{ $student->id }}, {{ $month->id }}, @js($cat['key']), $event.target.value)"
                                                   value="{{ $grades[$student->id][$month->id][$cat['key']] ?? '' }}"
                                                   class="w-full h-full border-none text-center text-sm focus:ring-2 focus:ring-inset focus:ring-purple-500 p-0 bg-transparent font-medium text-gray-700 dark:text-gray-300 placeholder-gray-200 dark:placeholder-gray-700 {{ isset($cat['is_attendance']) && $absenceWarning['warning'] ? 'text-red-600 font-bold bg-red-50 dark:bg-red-900/20' : '' }}"
                                                   placeholder="-">
                                        </td>
                                    @endforeach
                                    <!-- Month Total -->
                                    <td class="border-l border-gray-200 dark:border-gray-700 bg-purple-50/30 dark:bg-purple-900/10 text-center font-bold text-purple-700 dark:text-purple-400 text-sm">
                                        {{ $this->getStudentMonthTotal($student->id, $month->id) }}
                                    </td>
                                @endforeach

                                <!-- 5. Grand Total -->
                                <td class="bg-gray-50 dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 text-center sticky left-0 z-20 group-hover:bg-blue-50/50 dark:group-hover:bg-blue-900/20 shadow-[-4px_0_24px_rgba(0,0,0,0.02)]">
                                    <div class="flex flex-col items-center justify-center">
                                        <span class="font-bold {{ $isPassing ? 'text-gray-800 dark:text-gray-200' : 'text-red-600' }}">
                                            {{ $grandTotal }}
                                        </span>
                                        <span class="text-[10px] {{ $isPassing ? 'text-green-600' : 'text-red-500' }}">
                                            {{ $isPassing ? 'ناجح' : 'راسب' }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
    </div>

    <!-- Add Category Modal -->
    @if($showAddCategoryModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="$set('showAddCategoryModal', false)">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">إضافة قسم جديد</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">اسم القسم</label>
                        <input type="text" wire:model="newCategoryName" 
                               class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600"
                               placeholder="مثال: مشروع">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">الدرجة العظمى</label>
                        <input type="number" wire:model="newCategoryMaxScore" 
                               class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600"
                               min="1" max="100">
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button wire:click="$set('showAddCategoryModal', false)" 
                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        إلغاء
                    </button>
                    <button wire:click="addCustomCategory" 
                            class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition">
                        إضافة
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

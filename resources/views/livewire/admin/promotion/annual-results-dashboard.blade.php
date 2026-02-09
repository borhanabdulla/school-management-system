<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6" dir="rtl"
    x-data="{
        showFailedSubjectsModal: false,
        selectedStudentName: '',
        failedSubjects: [],
        showSidebar: true
    }">
    
    <!-- Grades Status Sidebar -->
    <div x-show="showSidebar" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-x-full"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 -translate-x-full"
        class="fixed right-4 top-24 w-64 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden z-40 border border-gray-200 dark:border-gray-700">
        
        <div class="px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex items-center justify-between">
            <span class="font-bold flex items-center gap-2">
                <span>📋</span> حالة الصفوف
            </span>
            <button @click="showSidebar = false" class="p-1 hover:bg-white/20 rounded transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div class="max-h-[60vh] overflow-y-auto">
            @foreach($this->gradesStatus as $grade)
                <button wire:click="filterByGrade({{ $grade['id'] }})"
                    class="w-full px-4 py-3 border-b dark:border-gray-700 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition
                        {{ $selectedGradeId == $grade['id'] ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                    <div class="text-right">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $grade['name'] }}</span>
                        <span class="block text-xs text-gray-500">
                            {{ $grade['with_results'] }}/{{ $grade['enrolled'] }} طالب
                        </span>
                    </div>
                    <span class="text-xl">
                        @if($grade['status'] === 'complete')
                            ✅
                        @elseif($grade['status'] === 'partial')
                            ⏳
                        @else
                            ❌
                        @endif
                    </span>
                </button>
            @endforeach
            
            <!-- Show All Button -->
            <button wire:click="filterByGrade(null)"
                class="w-full px-4 py-3 text-center text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 font-medium transition">
                عرض الكل
            </button>
        </div>
    </div>
    
    <!-- Toggle Sidebar Button (when hidden) -->
    <button x-show="!showSidebar" @click="showSidebar = true"
        class="fixed right-4 top-24 p-3 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg z-40 transition">
        📋
    </button>
    
    <!-- Main Content -->
    <div :class="showSidebar ? 'mr-72' : ''" class="transition-all duration-300">
    
    <!-- Header with Stepper -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">النتائج السنوية</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">تجميع نتائج الترمين وحساب قرارات الترحيل</p>
            </div>
            <a href="{{ route('promotion.manage') }}" 
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition flex items-center gap-2">
                <span>الانتقال للترحيل</span>
                <svg class="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
        
        <!-- Stepper -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-center">
                <!-- Step 1 -->
                <div class="flex items-center">
                    <div class="flex flex-col items-center">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-xl font-bold transition-all duration-300
                            {{ $this->currentStep > 1 ? 'bg-green-500 text-white' : ($this->currentStep === 1 ? 'bg-blue-500 text-white ring-4 ring-blue-200 dark:ring-blue-900' : 'bg-gray-200 dark:bg-gray-700 text-gray-500') }}">
                            @if($this->currentStep > 1)
                                ✓
                            @else
                                📊
                            @endif
                        </div>
                        <span class="text-sm mt-2 font-medium {{ $this->currentStep >= 1 ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}">
                            تجميع الدرجات
                        </span>
                    </div>
                </div>
                
                <!-- Connector 1 -->
                <div class="w-24 h-1 mx-4 rounded {{ $this->currentStep > 1 ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                
                <!-- Step 2 -->
                <div class="flex items-center">
                    <div class="flex flex-col items-center">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-xl font-bold transition-all duration-300
                            {{ $this->currentStep > 2 ? 'bg-green-500 text-white' : ($this->currentStep === 2 ? 'bg-blue-500 text-white ring-4 ring-blue-200 dark:ring-blue-900' : 'bg-gray-200 dark:bg-gray-700 text-gray-500') }}">
                            @if($this->currentStep > 2)
                                ✓
                            @else
                                ⚖️
                            @endif
                        </div>
                        <span class="text-sm mt-2 font-medium {{ $this->currentStep >= 2 ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}">
                            حساب القرارات
                        </span>
                    </div>
                </div>
                
                <!-- Connector 2 -->
                <div class="w-24 h-1 mx-4 rounded {{ $this->currentStep > 2 ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                
                <!-- Step 3 -->
                <div class="flex items-center">
                    <div class="flex flex-col items-center">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-xl font-bold transition-all duration-300
                            {{ $this->currentStep === 3 ? 'bg-green-500 text-white ring-4 ring-green-200 dark:ring-green-900' : 'bg-gray-200 dark:bg-gray-700 text-gray-500' }}">
                            🖨️
                        </div>
                        <span class="text-sm mt-2 font-medium {{ $this->currentStep >= 3 ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}">
                            جاهز للترحيل
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Step Description -->
            <div class="mt-4 text-center">
                @if($this->currentStep === 1)
                    <p class="text-blue-600 dark:text-blue-400">
                        📌 اضغط على "تجميع نتائج الترمين" لبدء التجميع
                    </p>
                @elseif($this->currentStep === 2)
                    <p class="text-blue-600 dark:text-blue-400">
                        📌 اضغط على "حساب القرارات" لتحديد الناجحين والراسبين
                    </p>
                @else
                    <p class="text-green-600 dark:text-green-400">
                        ✅ جاهز! يمكنك الانتقال لصفحة الترحيل أو طباعة الشهادات
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <!-- Filters & Actions -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
            <!-- Year Select -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">السنة الدراسية</label>
                <select wire:model.live="selectedYearId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">اختر السنة</option>
                    @foreach($this->academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Grade Select -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">الصف</label>
                <select wire:model.live="selectedGradeId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">كل الصفوف</option>
                    @foreach($this->grades as $grade)
                        <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Class Section Select (appears when grade is selected) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">الشعبة</label>
                <select wire:model.live="selectedSectionId" 
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white disabled:opacity-50"
                    {{ !$selectedGradeId ? 'disabled' : '' }}>
                    <option value="">كل الشعب</option>
                    @foreach($this->classSections as $section)
                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                    @endforeach
                </select>
                @if(!$selectedGradeId)
                    <p class="text-xs text-gray-400 mt-1">اختر الصف أولاً</p>
                @endif
            </div>

            <!-- Decision Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">القرار</label>
                <select wire:model.live="decisionFilter" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="all">الكل</option>
                    <option value="pending">قيد الانتظار</option>
                    <option value="pass">ناجح</option>
                    <option value="conditional">مُكمِّل</option>
                    <option value="fail">راسب</option>
                </select>
            </div>

            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">بحث</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="اسم الطالب أو الرقم..."
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3">
            <button wire:click="exportCsv" wire:loading.attr="disabled"
                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>تصدير Excel</span>
            </button>
            <button wire:click="aggregateResults" wire:loading.attr="disabled"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                <span wire:loading.remove wire:target="aggregateResults">📊 تجميع نتائج الترمين</span>
                <span wire:loading wire:target="aggregateResults">جاري التجميع...</span>
            </button>
            <button wire:click="calculateDecisions" wire:loading.attr="disabled"
                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition">
                <span wire:loading.remove wire:target="calculateDecisions">⚖️ حساب القرارات</span>
                <span wire:loading wire:target="calculateDecisions">جاري الحساب...</span>
            </button>
        </div>
    </div>

    <!-- Bulk Actions Toolbar -->
    @if(!empty($selectedResults))
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 mb-6 flex items-center justify-between">
            <div class="flex items-center gap-2 text-blue-800 dark:text-blue-200">
                <span class="font-bold">{{ count($selectedResults) }}</span> طالب محدد
            </div>
            <div class="flex gap-2">
                <button wire:click="bulkUpdateDecision('pass')" wire:confirm="هل أنت متأكد من تغيير قرار الطلاب المحددين إلى 'ناجح'؟" class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm transition">ناجح للكل</button>
                <button wire:click="bulkUpdateDecision('conditional')" wire:confirm="هل أنت متأكد من تغيير قرار الطلاب المحددين إلى 'مُكمِّل'؟" class="px-3 py-1 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm transition">مُكمِّل للكل</button>
                <button wire:click="bulkUpdateDecision('fail')" wire:confirm="هل أنت متأكد من تغيير قرار الطلاب المحددين إلى 'راسب'؟" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm transition">راسب للكل</button>
            </div>
        </div>
    @endif

    <!-- Statistics -->
    @if($this->statistics)
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow relative overflow-hidden">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->statistics['total'] }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">إجمالي الطلاب</div>
            <div class="absolute bottom-0 left-0 h-1 bg-gray-200 w-full"></div>
        </div>
        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-xl p-4 shadow relative overflow-hidden">
            <div class="text-2xl font-bold text-yellow-600">{{ $this->statistics['pending'] }}</div>
            <div class="text-sm text-yellow-700 dark:text-yellow-300">قيد الانتظار</div>
            <div class="absolute bottom-0 left-0 h-1 bg-yellow-200 w-full">
                <div class="h-full bg-yellow-500" style="width: {{ $this->statistics['total'] > 0 ? ($this->statistics['pending'] / $this->statistics['total']) * 100 : 0 }}%"></div>
            </div>
        </div>
        <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4 shadow relative overflow-hidden">
            <div class="text-2xl font-bold text-green-600">{{ $this->statistics['passed'] }}</div>
            <div class="text-sm text-green-700 dark:text-green-300">ناجح</div>
            <div class="absolute bottom-0 left-0 h-1 bg-green-200 w-full">
                <div class="h-full bg-green-500" style="width: {{ $this->statistics['total'] > 0 ? ($this->statistics['passed'] / $this->statistics['total']) * 100 : 0 }}%"></div>
            </div>
        </div>
        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-xl p-4 shadow relative overflow-hidden">
            <div class="text-2xl font-bold text-orange-600">{{ $this->statistics['conditional'] }}</div>
            <div class="text-sm text-orange-700 dark:text-orange-300">مُكمِّل</div>
            <div class="absolute bottom-0 left-0 h-1 bg-orange-200 w-full">
                <div class="h-full bg-orange-500" style="width: {{ $this->statistics['total'] > 0 ? ($this->statistics['conditional'] / $this->statistics['total']) * 100 : 0 }}%"></div>
            </div>
        </div>
        <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-4 shadow relative overflow-hidden">
            <div class="text-2xl font-bold text-red-600">{{ $this->statistics['failed'] }}</div>
            <div class="text-sm text-red-700 dark:text-red-300">راسب</div>
            <div class="absolute bottom-0 left-0 h-1 bg-red-200 w-full">
                <div class="h-full bg-red-500" style="width: {{ $this->statistics['total'] > 0 ? ($this->statistics['failed'] / $this->statistics['total']) * 100 : 0 }}%"></div>
            </div>
        </div>
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 shadow relative overflow-hidden">
            <div class="text-2xl font-bold text-blue-600">{{ number_format($this->statistics['average'], 1) }}%</div>
            <div class="text-sm text-blue-700 dark:text-blue-300">المتوسط</div>
            <div class="absolute bottom-0 left-0 h-1 bg-blue-200 w-full">
                <div class="h-full bg-blue-500" style="width: {{ $this->statistics['average'] }}%"></div>
            </div>
        </div>
    </div>
    @endif

    <!-- Results Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 w-10">
                        <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    </th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer hover:text-blue-600" wire:click="sortBy('rank')">
                        الترتيب
                        @if($sortField === 'rank') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                    </th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">الطالب</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">الصف</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300">الترم الأول</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300">الترم الثاني</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer hover:text-blue-600" wire:click="sortBy('annual_total')">
                        المجموع
                        @if($sortField === 'annual_total') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                    </th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer hover:text-blue-600" wire:click="sortBy('percentage')">
                        النسبة
                        @if($sortField === 'percentage') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                    </th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300">المواد الراسب</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300">القرار</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($this->results as $result)
                    @php
                        $rowBg = match($result->decision) {
                            'pass' => 'bg-green-50/50 dark:bg-green-900/10 hover:bg-green-100/70 dark:hover:bg-green-900/20',
                            'conditional' => 'bg-yellow-50/50 dark:bg-yellow-900/10 hover:bg-yellow-100/70 dark:hover:bg-yellow-900/20',
                            'fail' => 'bg-red-50/50 dark:bg-red-900/10 hover:bg-red-100/70 dark:hover:bg-red-900/20',
                            default => 'hover:bg-gray-50 dark:hover:bg-gray-700/50'
                        };
                    @endphp
                    <tr class="{{ $rowBg }} transition duration-150 ease-in-out">
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" wire:model.live="selectedResults" value="{{ $result->id }}" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($result->rank)
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full 
                                    {{ $result->rank <= 3 ? 'bg-yellow-100 text-yellow-800 font-bold' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $result->rank }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $result->student->full_name_ar }}</div>
                            <div class="text-sm text-gray-500">{{ $result->student->admission_number }}</div>
                            @if($result->student->currentClassSection)
                                <div class="text-xs text-gray-400">{{ $result->student->currentClassSection->name }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $result->grade->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $result->term1_total }}/{{ $result->term1_max }}</td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $result->term2_total }}/{{ $result->term2_max }}</td>
                        <td class="px-4 py-3 text-center font-bold text-gray-900 dark:text-white">{{ $result->annual_total }}/{{ $result->annual_max }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-sm font-medium 
                                {{ $result->percentage >= 60 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ number_format($result->percentage, 1) }}%
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($result->failed_count > 0)
                                <button 
                                    @click="
                                        selectedStudentName = '{{ $result->student->full_name_ar }}';
                                        failedSubjects = {{ json_encode($result->failed_subjects ?? []) }};
                                        showFailedSubjectsModal = true
                                    "
                                    class="px-3 py-1 bg-red-100 hover:bg-red-200 text-red-800 rounded-full text-sm transition cursor-pointer font-medium">
                                    {{ $result->failed_count }} مادة 📋
                                </button>
                            @else
                                <span class="text-green-600 text-lg">✓</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $decisionColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                    'pass' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                    'conditional' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                                    'fail' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                ];
                                $decisionLabels = [
                                    'pending' => 'قيد الانتظار',
                                    'pass' => 'ناجح',
                                    'conditional' => 'مُكمِّل',
                                    'fail' => 'راسب',
                                ];
                            @endphp
                            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $decisionColors[$result->decision] ?? '' }}">
                                {{ $decisionLabels[$result->decision] ?? $result->decision }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <!-- View Details -->
                                <button wire:click="viewDetails({{ $result->id }})" 
                                    class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition"
                                    title="التفاصيل">
                                    👁️
                                </button>
                                
                                <!-- Print Certificate -->
                                <a href="{{ route('promotion.annual-report-card', $result->id) }}" 
                                    target="_blank"
                                    class="p-2 text-green-600 hover:bg-green-100 rounded-lg transition"
                                    title="طباعة الشهادة">
                                    🖨️
                                </a>
                                
                                @if($result->decision === 'conditional')
                                    <!-- Quick Decision Buttons -->
                                    <button wire:click="updateDecision({{ $result->id }}, 'pass')" 
                                        wire:confirm="هل أنت متأكد من تغيير قرار الطالب إلى 'ناجح'؟"
                                        class="p-1.5 bg-green-600 hover:bg-green-700 text-white rounded text-xs"
                                        title="ناجح">
                                        ✓
                                    </button>
                                    <button wire:click="updateDecision({{ $result->id }}, 'fail')" 
                                        wire:confirm="هل أنت متأكد من تغيير قرار الطالب إلى 'راسب'؟"
                                        class="p-1.5 bg-red-600 hover:bg-red-700 text-white rounded text-xs"
                                        title="راسب">
                                        ✗
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center gap-3">
                                <span class="text-4xl">📊</span>
                                <span>لا توجد نتائج. قم بتجميع نتائج الترمين أولاً.</span>
                                <button wire:click="aggregateResults" class="mt-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                    تجميع النتائج الآن
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $this->results->links() }}
        </div>
    </div>

    <!-- Student Details Modal -->
    @if($showDetailsModal && $selectedResult)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showDetailsModal', false)"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:mr-4 sm:text-right w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                تفاصيل نتيجة الطالب: {{ $selectedResult->student->full_name_ar }}
                            </h3>
                            <div class="mt-4">
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                        <span class="block text-sm text-gray-500 dark:text-gray-400">الرقم الأكاديمي</span>
                                        <span class="block font-bold text-gray-900 dark:text-white">{{ $selectedResult->student->admission_number }}</span>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                        <span class="block text-sm text-gray-500 dark:text-gray-400">الصف</span>
                                        <span class="block font-bold text-gray-900 dark:text-white">{{ $selectedResult->grade->name }}</span>
                                    </div>
                                </div>

                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead>
                                        <tr>
                                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">البيان</th>
                                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الدرجة</th>
                                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">العظمى</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">الترم الأول</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-300">{{ $selectedResult->term1_total }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-300">{{ $selectedResult->term1_max }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">الترم الثاني</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-300">{{ $selectedResult->term2_total }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-300">{{ $selectedResult->term2_max }}</td>
                                        </tr>
                                        <tr class="bg-gray-50 dark:bg-gray-700 font-bold">
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">المجموع الكلي</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white">{{ $selectedResult->annual_total }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white">{{ $selectedResult->annual_max }}</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="mt-4 p-4 rounded-lg {{ $selectedResult->decision === 'pass' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : ($selectedResult->decision === 'fail' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' : 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300') }}">
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold">القرار النهائي:</span>
                                        <span class="text-lg font-bold">
                                            @if($selectedResult->decision === 'pass') ناجح
                                            @elseif($selectedResult->decision === 'fail') راسب
                                            @elseif($selectedResult->decision === 'conditional') مُكمِّل
                                            @else قيد الانتظار
                                            @endif
                                        </span>
                                    </div>
                                    @if($selectedResult->failed_count > 0)
                                        <div class="mt-2 text-sm">
                                            عدد المواد الراسب فيها: {{ $selectedResult->failed_count }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="$set('showDetailsModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        إغلاق
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    </div> {{-- End Main Content Wrapper --}}

    <!-- Failed Subjects Modal (Alpine.js) -->
    <div x-show="showFailedSubjectsModal" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
        @click.self="showFailedSubjectsModal = false"
        @keydown.escape.window="showFailedSubjectsModal = false"
        style="display: none;">
        
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-6 m-4"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span>📚</span> المواد الراسب فيها
                </h3>
                <button @click="showFailedSubjectsModal = false" 
                    class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <span class="text-sm text-gray-500 dark:text-gray-400">الطالب:</span>
                <span class="font-bold text-gray-900 dark:text-white mr-2" x-text="selectedStudentName"></span>
            </div>
            
            <div class="space-y-2 max-h-64 overflow-y-auto">
                <template x-for="(subject, index) in failedSubjects" :key="index">
                    <div class="flex justify-between items-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white" x-text="subject.subject_name || subject.name || 'مادة'"></span>
                        </div>
                        <div class="text-sm flex items-center gap-1">
                            <span class="text-red-600 font-bold" x-text="subject.total_score || subject.score || '0'"></span>
                            <span class="text-gray-400">/</span>
                            <span class="text-gray-600" x-text="subject.pass_score || subject.max || '50'"></span>
                        </div>
                    </div>
                </template>
                
                <template x-if="failedSubjects.length === 0">
                    <div class="text-center text-gray-500 py-4">
                        لا توجد تفاصيل متاحة
                    </div>
                </template>
            </div>
            
            <button @click="showFailedSubjectsModal = false" 
                class="mt-4 w-full py-2.5 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-lg transition font-medium">
                إغلاق
            </button>
        </div>
    </div>
</div>


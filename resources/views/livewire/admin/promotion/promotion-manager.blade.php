<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6" dir="rtl" x-data="{ showComposition: false }">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">إدارة الترحيل</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">ترحيل الطلاب للسنة الدراسية الجديدة</p>
        </div>
        <a href="{{ route('promotion.annual-results') }}" 
            class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
            </svg>
            <span>العودة للنتائج السنوية</span>
        </a>
    </div>

    <!-- Flash Messages -->
    <!-- Readiness Check Panel -->
    @if($selectedYearId)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span>🔍</span> فحص جاهزية الترحيل
        </h2>
        
        @if($this->readinessCheck['ready'])
            <div class="p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-lg mb-4 flex items-center gap-3">
                <span class="text-2xl">✅</span>
                <span class="font-bold">المدرسة جاهزة للترحيل الكامل</span>
            </div>
        @else
            <div class="p-4 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 rounded-lg mb-4 flex items-center gap-3">
                <span class="text-2xl">⚠️</span>
                <span class="font-bold">يوجد مشاكل يجب حلها قبل الترحيل</span>
            </div>
        @endif
        
        <!-- Issues List -->
        @if(!empty($this->readinessCheck['issues']))
            <div class="space-y-3 mb-4">
                @foreach($this->readinessCheck['issues'] as $issue)
                    <div class="flex items-center gap-3 p-3 rounded-lg 
                        {{ $issue['severity'] === 'critical' ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' : 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800' }}">
                        <span class="text-2xl">
                            {{ $issue['severity'] === 'critical' ? '🚫' : '⚠️' }}
                        </span>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $issue['message'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $issue['action'] }}</p>
                        </div>
                        @if(isset($issue['link']))
                            <a href="{{ route($issue['link']) }}" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition">
                                إصلاح
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
        
        <!-- Quick Stats -->
        @if(!empty($this->readinessCheck['stats']))
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-4">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->readinessCheck['stats']['total_students'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">إجمالي الطلاب</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $this->readinessCheck['stats']['with_results'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">لديهم نتائج</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $this->readinessCheck['stats']['expected_promoted'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">ناجح (سيُرحَّل)</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-orange-600">{{ $this->readinessCheck['stats']['expected_repeated'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">راسب (يعيد)</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $this->readinessCheck['stats']['remaining'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">متبقي للترحيل</div>
                </div>
            </div>
        @endif
        
        <!-- Action Buttons -->
        <div class="flex gap-3 flex-wrap">
            @if($this->readinessCheck['ready'])
                <button wire:click="promoteEntireSchool" 
                    wire:confirm="هل أنت متأكد من ترحيل جميع طلاب المدرسة؟ هذا الإجراء سيرحل {{ $this->readinessCheck['stats']['remaining'] ?? 0 }} طالب."
                    wire:loading.attr="disabled"
                    @if($promoting) disabled @endif
                    class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-lg font-bold transition shadow-lg flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="promoteEntireSchool">🚀 ترحيل المدرسة بالكامل</span>
                    <span wire:loading wire:target="promoteEntireSchool">جاري بدء الترحيل...</span>
                </button>
            @endif
            
            <button @click="showComposition = !showComposition" 
                class="px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition flex items-center gap-2">
                <span>📊</span>
                <span x-text="showComposition ? 'إخفاء معاينة الصفوف' : 'معاينة تكوين السنة الجديدة'"></span>
            </button>
        </div>
    </div>
    @endif

    <!-- Progress Bar -->
    @if($promoting)
        <div wire:poll.2s="checkPromotionProgress" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                <span class="animate-spin">⏳</span> جاري الترحيل...
            </h3>
            <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700 mb-2 overflow-hidden">
                <div class="bg-blue-600 h-4 rounded-full transition-all duration-500 ease-out" 
                     style="width: {{ $promotionProgress['percentage'] ?? 0 }}%"></div>
            </div>
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 font-medium">
                <span>{{ $promotionProgress['message'] ?? 'جاري المعالجة...' }}</span>
                <span>{{ $promotionProgress['percentage'] ?? 0 }}%</span>
            </div>
            @if(isset($promotionProgress['errors']) && count($promotionProgress['errors']) > 0)
                <div class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                    <h4 class="font-bold text-red-800 dark:text-red-300 mb-2">الأخطاء الأخيرة:</h4>
                    <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-400">
                        @foreach($promotionProgress['errors'] as $error)
                            <li>{{ $error['student_name'] }}: {{ $error['error'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif
    
    <!-- New Year Composition Preview -->
    <div x-show="showComposition" x-collapse class="mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span>📊</span> معاينة تكوين الصفوف للسنة الجديدة
            </h3>
            
            @if(!empty($this->newYearComposition))
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($this->newYearComposition as $item)
                        <div class="border dark:border-gray-700 rounded-xl p-4 {{ $item['status'] === 'overflow' ? 'border-red-300 bg-red-50 dark:bg-red-900/20' : 'border-gray-200' }}">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-bold text-gray-900 dark:text-white">{{ $item['grade_name'] }}</h4>
                                @if($item['status'] === 'overflow')
                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">تجاوز السعة</span>
                                @else
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">✓</span>
                                @endif
                            </div>
                            
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">القادمون (ناجحون):</span>
                                    <span class="font-medium text-green-600">{{ $item['promoted'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">الباقون (راسبون):</span>
                                    <span class="font-medium text-orange-600">{{ $item['repeaters'] }}</span>
                                </div>
                                <div class="flex justify-between border-t dark:border-gray-700 pt-2">
                                    <span class="text-gray-900 dark:text-white font-bold">الإجمالي:</span>
                                    <span class="font-bold">{{ $item['total'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">السعة المتاحة:</span>
                                    <span class="{{ $item['total'] > $item['capacity'] ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">
                                        {{ $item['capacity'] }} ({{ $item['sections_count'] }} شعب)
                                    </span>
                                </div>
                                @if($item['overflow'] > 0)
                                    <div class="flex justify-between text-red-600">
                                        <span>التجاوز:</span>
                                        <span class="font-bold">+{{ $item['overflow'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">لا توجد بيانات متاحة</p>
            @endif
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">السنة الدراسية (الحالية)</label>
                <select wire:model.live="selectedYearId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @foreach($this->academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">الصف</label>
                <select wire:model.live="selectedGradeId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">كل الصفوف</option>
                    @foreach($this->grades as $grade)
                        <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">الحالة</label>
                <select wire:model.live="statusFilter" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="pending">لم يُرحَّل</option>
                    <option value="promoted">تم ترحيله</option>
                    <option value="all">الكل</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">بحث</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="اسم الطالب..."
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 flex-wrap">
            @if($this->nextYear)
                <button wire:click="$set('showBulkPromoteModal', true)" 
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition disabled:opacity-50"
                    {{ empty($selectedStudents) ? 'disabled' : '' }}>
                    🚀 ترحيل المحددين ({{ count($selectedStudents) }})
                </button>
                <button wire:click="autoDistribute" 
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                    📤 توزيع تلقائي على الشعب
                </button>
            @endif
            <button wire:click="closeYearAndActivateNext" 
                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition"
                wire:confirm="هل أنت متأكد من إغلاق السنة الحالية وتفعيل السنة الجديدة؟">
                🔒 إغلاق السنة وتفعيل الجديدة
            </button>
        </div>
    </div>

    <!-- Statistics -->
    @if($this->statistics)
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->statistics['total'] }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">إجمالي الترحيل</div>
        </div>
        <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4 shadow">
            <div class="text-2xl font-bold text-green-600">{{ $this->statistics['promoted'] }}</div>
            <div class="text-sm text-green-700 dark:text-green-300">ناجح ومُرحَّل</div>
        </div>
        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-xl p-4 shadow">
            <div class="text-2xl font-bold text-orange-600">{{ $this->statistics['repeated'] }}</div>
            <div class="text-sm text-orange-700 dark:text-orange-300">راسب (يعيد)</div>
        </div>
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 shadow">
            <div class="text-2xl font-bold text-blue-600">{{ $this->statistics['graduated'] }}</div>
            <div class="text-sm text-blue-700 dark:text-blue-300">متخرج</div>
        </div>
        <div class="bg-gray-100 dark:bg-gray-700 rounded-xl p-4 shadow">
            <div class="text-2xl font-bold text-gray-600 dark:text-gray-300">{{ $this->statistics['transferred'] + $this->statistics['withdrawn'] }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">منقول/منسحب</div>
        </div>
        <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-4 shadow">
            <div class="text-2xl font-bold text-red-600">{{ $this->statistics['certificate_blocked'] }}</div>
            <div class="text-sm text-red-700 dark:text-red-300">شهادة محجوبة</div>
        </div>
    </div>
    @endif

    <!-- Students Table -->
    @if($statusFilter === 'pending')
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden mb-6">
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
            <h2 class="font-bold text-gray-900 dark:text-white">طلاب لم يتم ترحيلهم</h2>
        </div>
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-center">
                        <input type="checkbox" wire:model.live="selectAll" class="rounded">
                    </th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">الطالب</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">الصف الحالي</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300">القرار</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300">الصف التالي</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($this->students as $student)
                    @php
                        $annualResult = $student->annualResults->first();
                        $nextGrade = $annualResult?->canBePromoted() ? $student->currentGrade?->nextGrade : $student->currentGrade;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" wire:model.live="selectedStudents" value="{{ $student->id }}" class="rounded">
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $student->full_name_ar }}</div>
                            <div class="text-sm text-gray-500">{{ $student->admission_number }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $student->currentGrade?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($annualResult)
                                @php
                                    $colors = ['pass' => 'green', 'conditional' => 'orange', 'fail' => 'red', 'pending' => 'yellow'];
                                    $labels = ['pass' => 'ناجح', 'conditional' => 'مُكمِّل', 'fail' => 'راسب', 'pending' => 'قيد الانتظار'];
                                    $decisionKey = $annualResult->decision instanceof \BackedEnum ? $annualResult->decision->value : (string) $annualResult->decision;
                                @endphp
                                <span class="px-2 py-1 rounded-full text-sm bg-{{ $colors[$decisionKey] ?? 'gray' }}-100 text-{{ $colors[$decisionKey] ?? 'gray' }}-800">
                                    {{ $labels[$decisionKey] ?? $decisionKey }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">
                            {{ $nextGrade?->name ?? 'تخرّج' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($this->nextYear && $annualResult && $decisionKey !== 'pending')
                                <button wire:click="promoteStudent({{ $student->id }})" 
                                    class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-sm">
                                    ترحيل
                                </button>
                            @else
                                <span class="text-gray-400 text-sm">غير جاهز</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                            لا يوجد طلاب في انتظار الترحيل
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $this->students->links() }}
        </div>
    </div>
    @endif

    <!-- Promotions Table -->
    @if($statusFilter !== 'pending')
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
            <h2 class="font-bold text-gray-900 dark:text-white">سجلات الترحيل</h2>
        </div>
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">الطالب</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300">من</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300">إلى</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300">النوع</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300">الشهادة</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300">التاريخ</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($this->promotions as $promotion)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $promotion->student->full_name_ar }}</div>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $promotion->fromGrade?->name }}</td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $promotion->toGrade?->name ?? 'تخرّج' }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $typeColors = ['promoted' => 'green', 'repeated' => 'orange', 'graduated' => 'blue', 'transferred' => 'gray', 'withdrawn' => 'gray'];
                                $typeLabels = ['promoted' => 'ناجح', 'repeated' => 'يعيد', 'graduated' => 'متخرج', 'transferred' => 'منقول', 'withdrawn' => 'منسحب'];
                                $typeKey = $promotion->type instanceof \BackedEnum ? $promotion->type->value : (string) $promotion->type;
                            @endphp
                            <span class="px-2 py-1 rounded-full text-sm bg-{{ $typeColors[$typeKey] ?? 'gray' }}-100 text-{{ $typeColors[$typeKey] ?? 'gray' }}-800">
                                {{ $typeLabels[$typeKey] ?? $typeKey }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($promotion->certificate_blocked)
                                <span class="text-red-600">🚫 محجوبة</span>
                            @else
                                <span class="text-green-600">✓</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ $promotion->processed_at?->format('Y-m-d') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="openRevertModal({{ $promotion->id }})" 
                                class="px-2 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded text-sm">
                                تراجع
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                            لا توجد سجلات ترحيل
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    <!-- Revert Modal -->
    @if($showRevertModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">التراجع عن الترحيل</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">سيتم إعادة الطالب إلى صفه السابق وحذف التسجيل الجديد.</p>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">سبب التراجع *</label>
                <textarea wire:model="revertReason" rows="3" 
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    placeholder="اكتب سبب التراجع..."></textarea>
            </div>
            
            <div class="flex gap-3 justify-end">
                <button wire:click="$set('showRevertModal', false)" 
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg">
                    إلغاء
                </button>
                <button wire:click="revertPromotion" 
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                    تأكيد التراجع
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

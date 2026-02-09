<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">إدارة قوالب الدوام</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">تعريف أوقات الحصص والاستراحات لكل مرحلة دراسية</p>
            </div>
            <button wire:click="create" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                إنشاء قالب جديد
            </button>
        </div>

        {{-- Filters --}}
        <div class="mb-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Search --}}
                <div>
                    <input type="text" wire:model.live.debounce.300ms="search" 
                           placeholder="بحث بالاسم..."
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
                
                {{-- Year Filter --}}
                <div>
                    <select wire:model.live="filterYearId" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">كل السنوات</option>
                        @foreach($this->academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                {{-- Stage Filter --}}
                <div>
                    <select wire:model.live="filterStageId" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">كل المراحل</option>
                        @foreach($this->stages as $stage)
                            <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                {{-- Status Filter --}}
                <div>
                    <select wire:model.live="filterStatus" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="all">كل الحالات</option>
                        <option value="draft">مسودة</option>
                        <option value="active">نشط</option>
                        <option value="archived">مؤرشف</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Templates Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($this->templates as $template)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                    {{-- Header --}}
                    <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $template->name }}</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $template->academicYear?->name }}</p>
                            </div>
                            
                            {{-- Status Badge --}}
                            @php $status = $template->status; @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $status->value === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                {{ $status->value === 'draft' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' : '' }}
                                {{ $status->value === 'archived' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}">
                                {{ $status->label() }}
                            </span>
                        </div>
                    </div>
                    
                    {{-- Body --}}
                    <div class="p-5 space-y-4">
                        {{-- Info Grid --}}
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">المرحلة:</span>
                                <span class="mr-1 text-gray-900 dark:text-white">{{ $template->educationalStage?->name ?? 'عام' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">الحصص:</span>
                                <span class="mr-1 text-gray-900 dark:text-white">{{ $template->time_slots_count }}</span>
                            </div>
                        </div>
                        
                        {{-- Grades --}}
                        @if($template->grades->count() > 0)
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">الصفوف المعينة:</span>
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach($template->grades->take(4) as $grade)
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                            {{ $grade->name }}
                                        </span>
                                    @endforeach
                                    @if($template->grades->count() > 4)
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            +{{ $template->grades->count() - 4 }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif
                        
                        {{-- Default Badge --}}
                        @if($template->is_default)
                            <div class="flex items-center text-sm text-amber-600 dark:text-amber-400">
                                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                القالب الافتراضي
                            </div>
                        @endif
                    </div>
                    
                    {{-- Actions --}}
                    <div class="px-5 py-3 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <button wire:click="edit({{ $template->id }})" class="p-2 text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors" title="تعديل">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button wire:click="duplicate({{ $template->id }})" class="p-2 text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400 transition-colors" title="نسخ">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            @if($template->status->value === 'draft')
                                <button wire:click="activate({{ $template->id }})" wire:confirm="هل تريد تفعيل هذا القالب؟" class="px-3 py-1.5 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                                    تفعيل
                                </button>
                            @endif
                            
                            @if($template->status->value === 'active')
                                <button wire:click="archive({{ $template->id }})" wire:confirm="هل تريد أرشفة هذا القالب؟" class="px-3 py-1.5 text-sm bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                                    أرشفة
                                </button>
                            @endif
                            
                            @if($template->status->value !== 'active')
                                <button wire:click="confirmDelete({{ $template->id }})" class="p-2 text-red-500 hover:text-red-700 transition-colors" title="حذف">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">لا توجد قوالب</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">ابدأ بإنشاء قالب دوام جديد</p>
                    <div class="mt-6">
                        <button wire:click="create" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700">
                            إنشاء قالب جديد
                        </button>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $this->templates->links() }}
        </div>
    </div>

    {{-- Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ activeDay: @entangle('activeDay') }">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 transition-opacity" wire:click="closeModal"></div>

                {{-- Modal Content --}}
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl transform transition-all w-full max-w-4xl mx-auto">
                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ $isEditing ? 'تعديل القالب' : 'إنشاء قالب جديد' }}
                            </h2>
                            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Wizard Steps --}}
                        <div class="mt-4 flex items-center justify-center gap-4">
                            @foreach([1 => 'الإعدادات', 2 => 'الهيكل', 3 => 'الصفوف'] as $step => $label)
                                <button wire:click="goToStep({{ $step }})" 
                                        class="flex items-center gap-2 {{ $wizardStep >= $step ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400' }}">
                                    <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium border-2
                                        {{ $wizardStep >= $step ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-300 dark:border-gray-600' }}">
                                        {{ $step }}
                                    </span>
                                    <span class="hidden sm:inline font-medium">{{ $label }}</span>
                                </button>
                                @if($step < 3)
                                    <div class="w-12 h-0.5 {{ $wizardStep > $step ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600' }}"></div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="p-6 max-h-[60vh] overflow-y-auto">
                        {{-- Step 1: Settings --}}
                        @if($wizardStep === 1)
                            <div class="space-y-6">
                                {{-- Name --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">اسم القالب *</label>
                                    <input type="text" wire:model="form.name" 
                                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                           placeholder="مثال: الدوام الشتوي">
                                    @error('form.name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    {{-- Academic Year --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">السنة الدراسية *</label>
                                        <select wire:model="form.academic_year_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                            @foreach($this->academicYears as $year)
                                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Stage --}}
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">المرحلة (اختياري)</label>
                                        <select wire:model="form.educational_stage_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                            <option value="">عام لجميع المراحل</option>
                                            @foreach($this->stages as $stage)
                                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Working Days --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">أيام العمل *</label>
                                    <div class="flex flex-wrap gap-3">
                                        @foreach($this->days as $day)
                                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" wire:model="form.working_days" value="{{ $day->value }}"
                                                       class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $day->label() }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('form.working_days') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>

                                {{-- Description --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">الوصف (اختياري)</label>
                                    <textarea wire:model="form.description" rows="2"
                                              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                              placeholder="ملاحظات إضافية..."></textarea>
                                </div>

                                {{-- Is Default --}}
                                <div>
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="form.is_default"
                                               class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">تعيين كقالب افتراضي</span>
                                    </label>
                                </div>
                            </div>
                        @endif

                        {{-- Step 2: Structure --}}
                        @if($wizardStep === 2)
                            <div class="space-y-6">
                                {{-- Generator Card --}}
                                <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-5 border border-indigo-100 dark:border-indigo-800">
                                    <h3 class="text-lg font-semibold text-indigo-900 dark:text-indigo-100 mb-4">🚀 المولد الذكي</h3>
                                    
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                        <div>
                                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">وقت البداية</label>
                                            <input type="time" wire:model="form.generator_start_time" 
                                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">مدة الحصة (دقيقة)</label>
                                            <input type="number" wire:model="form.generator_slot_duration" min="30" max="60"
                                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">عدد الحصص</label>
                                            <input type="number" wire:model="form.generator_number_of_slots" min="4" max="10"
                                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                                        </div>
                                        <div class="flex items-end">
                                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox" wire:model="form.generator_include_assembly"
                                                       class="w-4 h-4 rounded border-gray-300 text-indigo-600">
                                                <span class="text-sm text-gray-700 dark:text-gray-300">طابور صباحي</span>
                                            </label>
                                        </div>
                                    </div>

                                    {{-- Breaks --}}
                                    <div class="mb-4">
                                        <label class="block text-xs text-gray-600 dark:text-gray-400 mb-2">الاستراحات</label>
                                        <div class="space-y-2">
                                            @foreach($form->generator_breaks as $i => $break)
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">بعد الحصة</span>
                                                    <input type="number" wire:model="form.generator_breaks.{{ $i }}.after_slot" min="1" max="10"
                                                           class="w-16 px-2 py-1 rounded border text-sm">
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">مدة</span>
                                                    <input type="number" wire:model="form.generator_breaks.{{ $i }}.duration" min="5" max="30"
                                                           class="w-16 px-2 py-1 rounded border text-sm">
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">دقيقة</span>
                                                    <button wire:click="form.removeBreak({{ $i }})" class="text-red-500 hover:text-red-700">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            @endforeach
                                            <button wire:click="form.addBreak" class="text-sm text-indigo-600 hover:text-indigo-800">+ إضافة استراحة</button>
                                        </div>
                                    </div>

                                    <button wire:click="generateSlots" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                                        ✨ توليد الجدول تلقائياً
                                    </button>
                                </div>

                                {{-- Day Tabs --}}
                                @if(count($form->slots) > 0)
                                    <div>
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="flex gap-2 overflow-x-auto pb-2">
                                                @foreach($form->working_days as $day)
                                                    <button wire:click="$set('activeDay', {{ $day }})"
                                                            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap
                                                                {{ $activeDay === $day ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                                                        {{ \App\Domains\Shared\Enums\DayOfWeek::tryFrom($day)?->label() ?? $day }}
                                                    </button>
                                                @endforeach
                                            </div>
                                            <button wire:click="applyToAllDays" wire:confirm="سيتم نسخ حصص هذا اليوم لجميع الأيام. هل تريد المتابعة؟"
                                                    class="px-4 py-2 text-sm bg-amber-100 hover:bg-amber-200 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200 rounded-lg transition-colors">
                                                📋 تطبيق على الكل
                                            </button>
                                        </div>

                                        {{-- Slots Table --}}
                                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4">
                                            <table class="w-full text-sm">
                                                <thead>
                                                    <tr class="text-gray-500 dark:text-gray-400">
                                                        <th class="text-right pb-3 w-8">#</th>
                                                        <th class="text-right pb-3">التسمية</th>
                                                        <th class="text-right pb-3">البداية</th>
                                                        <th class="text-right pb-3">النهاية</th>
                                                        <th class="text-right pb-3">النوع</th>
                                                        @if($attendanceMode === 'checkpoints')
                                                            <th class="text-center pb-3 w-20">تفتيش</th>
                                                        @endif
                                                        <th class="text-right pb-3 w-12"></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                                    @foreach($form->slots as $i => $slot)
                                                        @if(($slot['day_of_week'] ?? 0) === $activeDay)
                                                            <tr class="group">
                                                                <td class="py-2 text-gray-500">{{ ($slot['order_index'] ?? 0) + 1 }}</td>
                                                                <td class="py-2">
                                                                    <input type="text" wire:model="form.slots.{{ $i }}.label"
                                                                           class="w-full px-2 py-1 rounded border border-transparent hover:border-gray-300 focus:border-indigo-500 bg-transparent">
                                                                </td>
                                                                <td class="py-2">
                                                                    <input type="time" wire:model="form.slots.{{ $i }}.start_time"
                                                                           class="px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800">
                                                                </td>
                                                                <td class="py-2">
                                                                    <input type="time" wire:model="form.slots.{{ $i }}.end_time"
                                                                           class="px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800">
                                                                </td>
                                                                <td class="py-2">
                                                                    <select wire:model="form.slots.{{ $i }}.type"
                                                                            class="px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm">
                                                                        @foreach($this->slotTypes as $type)
                                                                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                @if($attendanceMode === 'checkpoints')
                                                                    <td class="py-2 text-center">
                                                                        <input type="checkbox" wire:model="form.slots.{{ $i }}.is_attendance_checkpoint"
                                                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                                    </td>
                                                                @endif
                                                                <td class="py-2">
                                                                    <button wire:click="removeSlot({{ $i }})" class="opacity-0 group-hover:opacity-100 text-red-500 hover:text-red-700 transition-opacity">
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                        </svg>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            <div class="mt-3 flex gap-2">
                                                <button wire:click="addSlot" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">+ إضافة حصة</button>
                                                <span class="text-gray-300">|</span>
                                                <button wire:click="addBreakSlot" class="text-sm text-amber-600 hover:text-amber-800 font-medium">+ إضافة استراحة</button>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-8 text-gray-500">
                                        استخدم المولد الذكي أعلاه لتوليد الحصص تلقائياً
                                    </div>
                                @endif
                                
                                @error('slots') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        {{-- Step 3: Grades --}}
                        @if($wizardStep === 3)
                            <div class="space-y-6">
                                <p class="text-sm text-gray-600 dark:text-gray-400">اختر الصفوف التي سيُطبق عليها هذا القالب:</p>
                                
                                @foreach($this->stages as $stage)
                                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4">
                                        <h4 class="font-medium text-gray-900 dark:text-white mb-3">{{ $stage->name }}</h4>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                            @foreach($this->grades->where('educational_stage_id', $stage->id) as $grade)
                                                @php $isAssigned = in_array($grade->id, $this->assignedGradeIds); @endphp
                                                <label class="inline-flex items-center gap-2 cursor-pointer {{ $isAssigned ? 'opacity-50' : '' }}">
                                                    <input type="checkbox" wire:model="form.grade_ids" value="{{ $grade->id }}"
                                                           {{ $isAssigned ? 'disabled' : '' }}
                                                           class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $grade->name }}</span>
                                                    @if($isAssigned)
                                                        <span class="text-xs text-red-500">(مُعين)</span>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                                
                                @error('form.grade_ids') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <div>
                            @if($wizardStep > 1)
                                <button wire:click="previousStep" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                                    → السابق
                                </button>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <button wire:click="closeModal" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                                إلغاء
                            </button>
                            @if($wizardStep < 3)
                                <button wire:click="nextStep" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                                    التالي ←
                                </button>
                            @else
                                <button wire:click="save" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                                    {{ $isEditing ? 'تحديث' : 'حفظ' }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 transition-opacity" wire:click="cancelDelete"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-lg text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600 dark:text-red-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:mr-4 sm:text-right">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                    تأكيد حذف القالب
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        هل أنت متأكد من رغبتك في حذف هذا القالب؟
                                    </p>
                                    @if($linkedSessionsCount > 0)
                                        <div class="mt-4 bg-red-50 dark:bg-red-900/20 p-3 rounded-lg border border-red-100 dark:border-red-800">
                                            <p class="text-sm font-medium text-red-800 dark:text-red-200 flex items-center gap-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                                تحذير هام!
                                            </p>
                                            <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                                                هذا القالب مرتبط بـ <strong>{{ $linkedSessionsCount }}</strong> حصة مجدولة فعلياً في الجداول الدراسية.
                                                <br>
                                                حذف القالب سيؤدي إلى <strong>حذف جميع هذه الحصص</strong> من جداول الفصول والمعلمين.
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="button" wire:click="delete" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            نعم، احذف القالب {{ $linkedSessionsCount > 0 ? 'وجميع الحصص' : '' }}
                        </button>
                        <button type="button" wire:click="cancelDelete" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            إلغاء
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

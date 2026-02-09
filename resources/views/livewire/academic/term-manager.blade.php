<div class="p-6 space-y-8">
    <div class="text-sm text-secondary dark:text-gray-400">
        <a href="{{ route('academic-years.index') }}" class="hover:text-primary transition-colors">السنوات الدراسية</a>
        <span class="mx-2 text-gray-400">/</span>
        <a href="{{ route('terms.index') }}" class="hover:text-primary transition-colors">الترمات</a>
        @if ($selectedYear)
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-primary dark:text-gray-200">{{ $selectedYear->name }}</span>
        @endif
    </div>
    {{-- 1. لوحة التحكم العلوية (Header & Controls) --}}
    <div
        class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-surface dark:bg-gray-900 p-4 rounded-2xl shadow-sm border border-border dark:border-gray-700 transition-colors duration-300">
        <div class="flex items-center gap-4 w-full md:w-auto">
            {{-- منتقي السنة الدراسية --}}
            <div class="relative min-w-[200px]">
                <label class="block text-xs font-bold text-secondary dark:text-gray-400 mb-1">السنة الدراسية</label>
                <select wire:model.live="filterYear"
                    class="w-full bg-background dark:bg-gray-800 border-none rounded-xl text-sm font-bold text-primary dark:text-white focus:ring-2 focus:ring-indigo-500/20 cursor-pointer">
                    <option value="">-- اختر السنة --</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}">
                            {{ $year->name }} {{ $year->status === \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Active ? '(النشطة)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- إحصائيات سريعة (تظهر فقط عند اختيار سنة) --}}
            @if ($selectedYear)
                <div class="hidden md:flex items-center gap-4 px-4 border-r border-border dark:border-gray-700 mr-4">
                    <div>
                        <p class="text-xs text-secondary dark:text-gray-400">عدد الفصول</p>
                        <p class="font-bold text-primary dark:text-white">{{ $selectedYear->terms->count() }} فصول</p>
                    </div>
                    <div>
                        <p class="text-xs text-secondary dark:text-gray-400">الحالة العامة</p>
                        <x-academic.status-badge :status="$selectedYear->status" />
                    </div>
                </div>
            @endif
        </div>

        {{-- زر الإجراء الرئيسي --}}
        <button wire:click="create"
            class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-indigo-500/30 flex items-center justify-center gap-2 transition-all hover:-translate-y-0.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
                    clip-rule="evenodd" />
            </svg>
            إضافة فصل دراسي
        </button>
    </div>

    @if ($selectedYear)
        {{-- 2. نظرة عامة على التقدم (Year Progress) --}}
        <x-academic.year-progress :selectedYear="$selectedYear" />

        {{-- 3. الخط الزمني التفاعلي (Interactive Timeline) --}}
        <x-academic.term-timeline :terms="$terms" />

        {{-- 4. منطقة الإجراءات السريعة (Quick Actions) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8">
            <button
                class="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl border border-indigo-100 dark:border-indigo-800 flex items-center gap-3 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition group">
                <div
                    class="w-10 h-10 bg-surface dark:bg-gray-900 rounded-lg flex items-center justify-center text-indigo-600 dark:text-indigo-400 shadow-sm group-hover:scale-110 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="text-right">
                    <h5 class="font-bold text-indigo-900 dark:text-indigo-300">التقويم الدراسي</h5>
                    <p class="text-xs text-indigo-600 dark:text-indigo-400">عرض التفاصيل الزمنية</p>
                </div>
            </button>
            {{-- المزيد من الأزرار الوهمية للعرض --}}
            <button
                class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-xl border border-purple-100 dark:border-purple-800 flex items-center gap-3 hover:bg-purple-100 dark:hover:bg-purple-900/30 transition group">
                <div
                    class="w-10 h-10 bg-surface dark:bg-gray-900 rounded-lg flex items-center justify-center text-purple-600 dark:text-purple-400 shadow-sm group-hover:scale-110 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div class="text-right">
                    <h5 class="font-bold text-purple-900 dark:text-purple-300">تقارير الأداء</h5>
                    <p class="text-xs text-purple-600 dark:text-purple-400">تحليل نتائج الفصول</p>
                </div>
            </button>
        </div>
    @else
        {{-- حالة عدم اختيار سنة (Empty State / Prompt) --}}
        <div
            class="text-center py-20 bg-surface dark:bg-gray-900 rounded-2xl border border-dashed border-border dark:border-gray-700 transition-colors duration-300">
            <div
                class="w-20 h-20 bg-indigo-50 dark:bg-indigo-900/20 rounded-full flex items-center justify-center mx-auto mb-6 text-indigo-500 dark:text-indigo-400">
                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-primary dark:text-white mb-2">اختر سنة دراسية للبدء</h3>
            <p class="text-secondary dark:text-gray-400 max-w-md mx-auto mb-6">قم باختيار السنة الدراسية من القائمة
                أعلاه لعرض رحلة الفصول
                الدراسية وإدارتها.</p>
        </div>
    @endif

    {{-- 5. المودال (Create / Edit) --}}
    <x-ui.modal wire:model="showModal" maxWidth="2xl">
        <div
            class="px-6 py-5 border-b border-border bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-900 dark:to-purple-900">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center shadow-lg backdrop-blur-sm">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">
                        {{ $isEditing ? 'تعديل الفصل الدراسي' : 'إضافة فصل دراسي جديد' }}
                    </h3>
                    <p class="text-xs text-indigo-100 mt-0.5">
                        {{ $isEditing ? 'قم بتحديث بيانات الفصل الدراسي' : 'أضف فصلاً دراسياً جديداً للسنة الأكاديمية' }}
                    </p>
                </div>
            </div>
        </div>

        <form wire:submit.prevent="save">
            <div class="px-6 py-6 space-y-6">
                {{-- اختيار السنة --}}
                <div
                    class="bg-indigo-50 dark:bg-indigo-900/10 p-4 rounded-xl border border-indigo-100 dark:border-indigo-800">
                    <label
                        class="block text-sm font-bold text-indigo-900 dark:text-indigo-300 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        السنة الدراسية
                    </label>
                    <select wire:model="form.academic_year_id" {{ $isEditing ? 'disabled' : '' }}
                        class="w-full rounded-xl border-indigo-200 dark:border-indigo-700 bg-white dark:bg-gray-800 text-primary dark:text-white shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 disabled:bg-gray-100 dark:disabled:bg-gray-700 disabled:cursor-not-allowed transition-all">
                        <option value="">اختر السنة الدراسية...</option>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}">
                                {{ $year->name }}
                                @if ($year->status === \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Active)
                                    <span class="text-green-600">(النشطة)</span>
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @if ($isEditing)
                        <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-2 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            لا يمكن تغيير السنة الدراسية عند التعديل
                        </p>
                    @endif
                    @error('form.academic_year_id')
                        <span class="text-red-500 text-xs mt-1 block flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- الاسم والترتيب --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label
                            class="block text-sm font-bold text-secondary dark:text-gray-300 mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-500" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            اسم الفصل الدراسي
                        </label>
                        <input type="text" wire:model="form.name" placeholder="مثال: الفصل الدراسي الأول"
                            class="w-full rounded-xl border-border dark:border-gray-700 bg-background dark:bg-gray-800 text-primary dark:text-white shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all placeholder-secondary/50">
                        @error('form.name')
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label
                            class="block text-sm font-bold text-secondary dark:text-gray-300 mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                            </svg>
                            الترتيب
                        </label>
                        <input type="number" wire:model="form.order_index" min="1"
                            class="w-full rounded-xl border-border dark:border-gray-700 bg-background dark:bg-gray-800 text-primary dark:text-white shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                        @error('form.order_index')
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                        <p class="text-xs text-secondary dark:text-gray-400 mt-1">ترتيب الفصل في السنة</p>
                    </div>
                </div>

                {{-- التواريخ --}}
                <div
                    class="bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/10 dark:to-cyan-900/10 p-4 rounded-xl border border-blue-100 dark:border-blue-800">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h4 class="font-bold text-blue-900 dark:text-blue-300">الفترة الزمنية</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">تاريخ
                                البداية</label>
                            <input type="date" wire:model="form.start_date"
                                class="w-full rounded-xl border-blue-200 dark:border-blue-700 bg-white dark:bg-gray-800 text-primary dark:text-white shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                            @error('form.start_date')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">تاريخ
                                النهاية</label>
                            <input type="date" wire:model="form.end_date"
                                class="w-full rounded-xl border-blue-200 dark:border-blue-700 bg-white dark:bg-gray-800 text-primary dark:text-white shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                            @error('form.end_date')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- الحالة --}}
                <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                    <label
                        class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        حالة الفصل الدراسي
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @foreach ($this->statuses as $statusOption)
                            @php
                                // Completed status happens automatically when activating another term
                                $allowedStatuses = [
                                    \App\Domains\Academic\Term\Enums\TermStatus::Pending,
                                    \App\Domains\Academic\Term\Enums\TermStatus::Active,
                                ];
                            @endphp
                            @if (!in_array($statusOption, $allowedStatuses, true))
                                @continue
                            @endif
                            <label
                                class="cursor-pointer relative rounded-xl border p-3 flex flex-col items-center gap-2 transition-all
                                {{ $form->status === $statusOption->value
                                    ? 'bg-indigo-50 border-indigo-500 text-indigo-700 dark:bg-indigo-900/20 dark:border-indigo-500 dark:text-indigo-300 ring-1 ring-indigo-500'
                                    : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700' }}">
                                <input type="radio" wire:model.live="form.status"
                                    value="{{ $statusOption->value }}" class="sr-only">
                                <span class="text-sm font-bold">{{ $statusOption->label() }}</span>
                                @if ($form->status === $statusOption->value)
                                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400 absolute top-2 right-2"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </label>
                        @endforeach
                    </div>

                    @if ($form->status === \App\Domains\Academic\Term\Enums\TermStatus::Active->value)
                        <div
                            class="mt-3 flex items-start gap-2 text-xs text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 p-3 rounded-lg border border-amber-200 dark:border-amber-800">
                            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>⚠️ تنبيه: تفعيل هذا الفصل سيؤدي لإغلاق أي فصل آخر نشط في نفس السنة الدراسية.</span>
                        </div>
                    @endif
                    @error('form.status')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div
                class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-border flex justify-between gap-3 rounded-b-xl">
                <button type="button" wire:click="$set('showModal', false)"
                    class="px-5 py-2.5 rounded-xl border border-border dark:border-gray-700 bg-white dark:bg-gray-800 text-secondary dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    إلغاء
                </button>
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold hover:from-indigo-700 hover:to-purple-700 shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/40 transition-all flex items-center gap-2 transform hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span wire:loading.remove>{{ $isEditing ? 'تحديث البيانات' : 'حفظ الفصل الدراسي' }}</span>
                    <span wire:loading>جاري الحفظ...</span>
                </button>
            </div>
        </form>
    </x-ui.modal>
</div>

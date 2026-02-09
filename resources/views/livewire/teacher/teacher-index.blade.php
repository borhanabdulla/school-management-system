<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Flash Messages --}}
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">دليل المعلمين</h2>
            <button class="mt-4 md:mt-0 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow flex items-center transition">
                <svg class="w-5 h-5 ml-2 rtl:ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                إضافة معلم جديد
            </button>
        </div>

        <!-- 
            🚀 Alpine.js Controller
            يقوم بإدارة الفلترة بين الصف والشعب محلياً بدون طلبات سيرفر
        -->
        <div 
            x-data="{
                gradeId: @entangle('filters.grade_id').live,
                sectionId: @entangle('filters.class_section_id').live,
                allSections: {{ \Illuminate\Support\Js::from($allSections) }},
                
                // Computed Property: ترجع الشعب التابعة للصف المختار فقط
                get filteredSections() {
                    if (!this.gradeId) return [];
                    return this.allSections.filter(s => s.grade_id == this.gradeId);
                },

                // عند تغيير الصف، نقوم بتصفير الشعبة المختارة
                resetSection() {
                    this.sectionId = '';
                }
            }"
            class="bg-white dark:bg-surface shadow rounded-lg mb-6 p-5 border border-gray-100 dark:border-border"
        >
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                
                <!-- 1. بحث عام -->
                <div class="lg:col-span-1">
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">بحث سريع</label>
                    <input wire:model.live.debounce.500ms="filters.search" type="text" class="w-full border-gray-300 dark:border-border dark:bg-surface dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="الاسم أو البريد...">
                </div>

                <!-- 2. السنة الدراسية -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">السنة الدراسية</label>
                    <select wire:model.live="filters.academic_year_id" class="w-full border-gray-300 dark:border-border dark:bg-surface dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        <option value="">كل السنوات</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 3. الصف الدراسي (Alpine Model) -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">الصف الدراسي</label>
                    <select 
                        x-model="gradeId" 
                        @change="resetSection()"
                        class="w-full border-gray-300 dark:border-border dark:bg-surface dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                    >
                        <option value="">كل الصفوف</option>
                        @foreach($grades as $grade)
                            <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 4. الشعبة (Dependent Filter) -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">الشعبة</label>
                    <select 
                        x-model="sectionId" 
                        class="w-full border-gray-300 dark:border-border dark:bg-surface dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm disabled:bg-gray-100 disabled:text-gray-400 dark:disabled:bg-slate-800 dark:disabled:text-slate-400"
                        :disabled="!gradeId || filteredSections.length === 0"
                    >
                        <option value="">كل الشعب</option>
                        <template x-for="section in filteredSections" :key="section.id">
                            <option :value="section.id" x-text="section.name"></option>
                        </template>
                    </select>
                </div>

                <!-- 5. المادة (Datalist) -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">المادة</label>
                    <input 
                        wire:model.live.debounce.500ms="filters.subject_name" 
                        list="subjectsList"
                        type="text" 
                        class="w-full border-gray-300 dark:border-border dark:bg-surface dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" 
                        placeholder="اختر المادة..."
                    >
                    <datalist id="subjectsList">
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->name }}">
                        @endforeach
                    </datalist>
                </div>

            </div>
        </div>

        <!-- Table & Loading -->
        <div class="relative bg-white dark:bg-surface shadow overflow-hidden sm:rounded-lg border border-gray-200 dark:border-border">
            
            <!-- Loading Overlay -->
            <div wire:loading.flex class="absolute inset-0 bg-white/80 dark:bg-surface/80 z-20 flex items-center justify-center">
                <div class="flex items-center space-x-2 rtl:space-x-reverse">
                    <svg class="animate-spin h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span class="text-indigo-600 font-medium text-sm">جاري التحديث...</span>
                </div>
            </div>

            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                <thead class="bg-gray-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-slate-300 uppercase tracking-wider">المعلم</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-slate-300 uppercase tracking-wider">التخصص</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-slate-300 uppercase tracking-wider">عبء العمل</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-slate-300 uppercase tracking-wider">تاريخ التعيين</th>
                        <th class="relative px-6 py-3"><span class="sr-only">إجراءات</span></th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-surface divide-y divide-gray-200 dark:divide-slate-700">
                    @forelse($teachers as $teacher)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/60 transition duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 dark:bg-slate-700 flex items-center justify-center text-indigo-600 dark:text-indigo-300 font-bold border border-indigo-200 dark:border-slate-600">
                                            {{ substr($teacher->staff->first_name ?? 'T', 0, 1) }}
                                        </div>
                                    </div>
                                    <div class="mr-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $teacher->staff->first_name }} {{ $teacher->staff->last_name }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-slate-400">
                                            {{-- ✅ البريد من users عبر staff.user --}}
                                            {{ $teacher->staff->user?->email ?? 'لا يوجد بريد' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-slate-200">
                                    {{ $teacher->specialization ?? 'عام' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2.5 py-0.5 rounded-md text-sm font-medium {{ $teacher->course_offerings_count > 0 ? 'bg-green-100 text-green-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-slate-700 dark:text-slate-300' }}">
                                    {{ $teacher->course_offerings_count }} حصة
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-300">
                                {{-- ✅ تاريخ التعيين من staff.joining_date مع حماية من null --}}
                                {{ $teacher->staff->joining_date ? \Carbon\Carbon::parse($teacher->staff->joining_date)->format('Y/m/d') : '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium">
                                <a href="{{ route('teachers.show', $teacher->id) }}" class="text-indigo-600 dark:text-indigo-300 hover:text-indigo-900 dark:hover:text-indigo-200 ml-3">الملف</a>
                                <button wire:click="delete({{ $teacher->id }})" 
                                        wire:confirm="هل أنت متأكد من حذف هذا المعلم؟"
                                        class="text-gray-400 dark:text-slate-400 hover:text-red-600 transition">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="h-12 w-12 text-gray-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span class="text-gray-500 dark:text-slate-400 font-medium">لا توجد نتائج تطابق بحثك</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800/60">
                {{ $teachers->links() }}
            </div>
        </div>
    </div>
</div>

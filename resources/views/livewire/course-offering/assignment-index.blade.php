<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <!-- Header Section -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        تعيين المعلمين للمواد
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">إدارة توزيع المعلمين على المقررات الدراسية</p>
                </div>
                
                <!-- Coverage Badge -->
                <div class="flex items-center gap-4">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl px-6 py-4 shadow-lg">
                        <div class="text-white text-center">
                            <div class="text-3xl font-bold">{{ $this->coverage }}%</div>
                            <div class="text-xs opacity-90 mt-1">نسبة التغطية</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Term Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الفصل الدراسي</label>
                    <select wire:model.live="filters.term_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        <option value="">جميع الفصول</option>
                        @foreach($this->terms as $term)
                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Grade Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الصف الدراسي</label>
                    <select wire:model.live="filters.grade_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        <option value="">جميع الصفوف</option>
                        @foreach($this->grades as $grade)
                            <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Section Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الشعبة</label>
                    <select wire:model.live="filters.section_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        <option value="">جميع الشعب</option>
                        @foreach($this->sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                المادة
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                الصف
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                الشعبة
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                المعلم المسند
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                الحالة
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($this->offerings as $offering)
                            <tr class="hover:bg-indigo-50/50 transition-colors duration-150" wire:key="offering-{{ $offering->id }}">
                                <!-- Subject -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $offering->subject->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $offering->subject->code }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Grade -->
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $offering->classSection->grade->name }}
                                    </span>
                                </td>

                                <!-- Section -->
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        {{ $offering->classSection->name }}
                                    </span>
                                </td>

                                <!-- Teacher Assignment -->
                                <td class="px-6 py-4">
                                    <div class="space-y-2">
                                        <div class="flex justify-between items-center">
                                            <label class="text-xs font-medium text-gray-600">اختر معلم</label>
                                            <button 
                                                wire:click="toggleShowAll({{ $offering->id }})"
                                                class="text-xs text-indigo-600 hover:text-indigo-800 font-medium transition">
                                                {{ ($showAllTeachers[$offering->id] ?? false) ? '🔽 إخفاء البعض' : '🔼 عرض الكل' }}
                                            </button>
                                        </div>
                                        <select 
                                            wire:model.live="form.teacher_id"
                                            wire:change="assignTeacher({{ $offering->id }})"
                                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition"
                                            wire:loading.attr="disabled">
                                            <option value="">-- اختر معلم --</option>
                                            @foreach($this->getFilteredTeachers($offering->subject_id, $offering->term_id, $offering->id) as $teacher)
                                                <option value="{{ $teacher['id'] }}" 
                                                    {{ $offering->teacher_id == $teacher['id'] ? 'selected' : '' }}
                                                    @if($teacher['is_overloaded']) style="color: #dc2626; font-weight: bold;" @endif>
                                                    @if(isset($teacher['priority']))
                                                        @if($teacher['priority'] == 1) ✅
                                                        @elseif($teacher['priority'] == 2) ⚠️
                                                        @else 📋
                                                        @endif
                                                    @endif
                                                    {{ $teacher['name'] }}
                                                    @if($teacher['specialization'] !== 'غير محدد')
                                                        ({{ $teacher['specialization'] }})
                                                    @endif
                                                    | {{ $teacher['current_load'] }}/{{ $teacher['max_load'] }} ({{ $teacher['load_percentage'] }}%)
                                                    @if($teacher['is_overloaded']) - محمّل @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 text-center">
                                    @if($offering->teacher_id)
                                        <div class="flex flex-col items-center gap-1.5">
                                            <span class="inline-flex items-center px-4 py-2 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200 shadow-sm">
                                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                مسند
                                            </span>
                                            <span class="text-xs font-medium text-gray-600">{{ $offering->teacher->staff->first_name ?? '' }} {{ $offering->teacher->staff->last_name ?? '' }}</span>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center px-4 py-2 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200 shadow-sm animate-pulse">
                                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            غير مسند
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="text-gray-500 font-medium">لا توجد مقررات دراسية للعرض</p>
                                        <p class="text-sm text-gray-400 mt-1">جرّب تغيير الفلاتر أعلاه</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($this->offerings->hasPages())
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    {{ $this->offerings->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Loading Overlay -->
    <div wire:loading class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-6 flex items-center gap-4">
            <svg class="animate-spin h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-700 font-medium">جاري التحميل...</span>
        </div>
    </div>
</div>
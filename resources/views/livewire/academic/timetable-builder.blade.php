<div class="py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Flash Messages -->
        <!-- Header + Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex flex-col gap-6">
                <div>
                    <h1 class="text-2xl font-black bg-gradient-to-l from-indigo-600 via-purple-600 to-teal-500 bg-clip-text text-transparent">
                        الجدول الدراسي
                    </h1>
                    <p class="mt-1 text-gray-600 dark:text-gray-400 text-sm">انقر على الخانة للإضافة أو التعديل</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">السنة الدراسية</label>
                        <select wire:model.live="selectedYearId" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm py-2.5">
                            <option value="">-- اختر السنة --</option>
                            @foreach($this->academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">الفصل الدراسي</label>
                        <select wire:model.live="selectedTermId" {{ !$selectedYearId ? 'disabled' : '' }} class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm py-2.5 disabled:opacity-50">
                            <option value="">-- اختر الفصل --</option>
                            @foreach($this->terms as $term)
                                <option value="{{ $term->id }}">{{ $term->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">الصف</label>
                        <select wire:model.live="selectedGradeId" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm py-2.5">
                            <option value="">-- اختر الصف --</option>
                            @foreach($this->grades as $grade)
                                <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">الشعبة</label>
                        <select wire:model.live="selectedSectionId" {{ !$selectedGradeId ? 'disabled' : '' }} class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm py-2.5 disabled:opacity-50">
                            <option value="">-- اختر الشعبة --</option>
                            @foreach($this->sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        @if($selectedSectionId)
            @if($this->template)
                <!-- Timetable Grid -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr class="bg-gradient-to-l from-indigo-600 to-purple-600">
                                    <th class="px-4 py-4 text-right text-sm font-bold text-white w-32 sticky left-0 z-10 bg-indigo-600">اليوم</th>
                                    @foreach($this->slotRows as $slot)
                                        <th class="px-4 py-4 text-center text-sm font-bold text-white min-w-[120px]">
                                            <div>{{ $slot->label }}</div>
                                            <div class="text-[10px] font-normal opacity-80 mt-1">{{ $slot->time_range }}</div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($this->workingDays as $day)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        {{-- Day Column --}}
                                        <td class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50 border-l border-gray-200 dark:border-gray-700 font-bold text-gray-800 dark:text-gray-200 text-sm sticky left-0 z-10">
                                            {{ $this->dayNames[$day] }}
                                        </td>
                                        
                                        {{-- Slots Columns --}}
                                        @foreach($this->slotRows as $slotRow)
                                            @php 
                                                // جلب الحصة الفعلية لليوم
                                                $actualSlot = $this->getSlotForDayAndOrder($day, $slotRow->order_index);
                                                $session = $actualSlot ? $this->timetableMatrix->get($actualSlot->id) : null;
                                                $isBreak = $actualSlot?->type === \App\Domains\Academic\Timetable\Enums\TimeSlotType::Break;
                                                $isAssembly = $actualSlot?->type === \App\Domains\Academic\Timetable\Enums\TimeSlotType::Assembly;
                                                $isPrayer = $actualSlot?->type === \App\Domains\Academic\Timetable\Enums\TimeSlotType::Prayer;
                                            @endphp

                                            <td class="px-2 py-2 text-center align-middle border-l border-gray-100 dark:border-gray-700 h-24 relative group">
                                                @if(!$actualSlot)
                                                    <div class="text-gray-300 text-xs">-</div>
                                                @elseif($isBreak || $isAssembly || $isPrayer)
                                                    <div class="h-full bg-gray-100 dark:bg-gray-700/50 rounded-xl flex flex-col items-center justify-center border border-gray-200 dark:border-gray-600">
                                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                            {{ $actualSlot->label }}
                                                        </span>
                                                    </div>
                                                @elseif($session)
                                                    <div wire:click="openModal({{ $actualSlot->id }}, {{ $day }})"
                                                        class="h-full bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/40 dark:to-purple-900/40 border border-indigo-200 dark:border-indigo-700 rounded-xl p-2 flex flex-col justify-center cursor-pointer hover:shadow-md transition-all relative">
                                                        <span class="font-bold text-indigo-700 dark:text-indigo-300 text-xs line-clamp-2">
                                                            {{ $session->courseOffering?->subject?->name ?? 'مادة' }}
                                                        </span>
                                                        <span class="text-[10px] text-indigo-500 dark:text-indigo-400 mt-1 truncate">
                                                            {{ $session->courseOffering?->teacher?->full_name ?? 'معلم' }}
                                                        </span>
                                                        @if(!$this->isReadOnly)
                                                            <button wire:click.stop="deleteSession({{ $actualSlot->id }})" wire:confirm="هل أنت متأكد من حذف هذه الحصة؟"
                                                                class="absolute -top-1.5 -left-1.5 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-all shadow-lg hover:bg-red-600 z-10">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                            </button>
                                                        @endif
                                                    </div>
                                                @else
                                                    <button wire:click="openModal({{ $actualSlot->id }}, {{ $day }})"
                                                        class="w-full h-full min-h-[60px] rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 hover:border-indigo-400 dark:hover:border-indigo-500 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/20 transition-all flex items-center justify-center group-hover:scale-[0.98]">
                                                        <svg class="w-6 h-6 text-gray-300 dark:text-gray-600 hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-16 text-center">
                    <div class="w-20 h-20 mx-auto rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-700 dark:text-gray-300 mb-2">لا يوجد قالب دوام معين</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">لم يتم تعيين قالب دوام لهذا الصف أو المرحلة الدراسية.</p>
                    <a href="{{ route('timetable-templates.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                        الذهاب لإعداد القوالب
                    </a>
                </div>
            @endif
        @else
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-16 text-center">
                <div class="w-20 h-20 mx-auto rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-700 dark:text-gray-300 mb-2">اختر الفلاتر أعلاه</h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm">السنة → الفصل → الصف → الشعبة</p>
            </div>
        @endif

        <!-- Modal -->
        @if($showModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ openTeacher: false, openSubject: false }">
                <div class="flex items-center justify-center min-h-screen px-4 py-6">
                    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeModal"></div>
                    
                    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg z-10">
                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">تعيين حصة</h3>
                                        @if($selectedDay !== null)
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $this->dayNames[$selectedDay] ?? '' }}</p>
                                        @endif
                                    </div>
                                </div>
                                <button wire:click="closeModal" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Conflict Warning -->
                            @if($conflictWarning)
                                <div class="mb-4 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700">
                                    <div class="flex items-start gap-3">
                                        <svg class="w-6 h-6 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="flex-1">
                                            <p class="font-bold text-amber-700 dark:text-amber-300">{{ $conflictWarning['message'] }}</p>
                                            <p class="text-sm text-amber-600 dark:text-amber-400 mt-1">{{ $conflictWarning['details'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="space-y-4">
                                <!-- Teacher Dropdown -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        <svg class="w-4 h-4 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        المعلم
                                    </label>
                                    <div class="relative" @click.away="openTeacher = false">
                                        <button type="button" @click="openTeacher = !openTeacher"
                                            class="w-full flex items-center justify-between gap-2 px-4 py-3 text-right bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                                            <span class="flex-1 truncate text-sm">
                                                @if($selectedTeacherId)
                                                    @php $teacher = $this->teachers->firstWhere('id', $selectedTeacherId); @endphp
                                                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ $teacher?->full_name ?? 'معلم' }}</span>
                                                @else
                                                    <span class="text-gray-400">-- اختر المعلم --</span>
                                                @endif
                                            </span>
                                            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{'rotate-180': openTeacher}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>

                                        <div x-show="openTeacher" x-transition class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl overflow-hidden">
                                            <div class="p-3 border-b border-gray-100 dark:border-gray-700">
                                                <input type="text" wire:model.live.debounce.300ms="searchTeacher" placeholder="ابحث عن معلم..."
                                                    class="w-full px-4 py-2 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500">
                                            </div>
                                            <div class="max-h-48 overflow-y-auto">
                                                @forelse($this->teachers as $teacher)
                                                    <button type="button"
                                                        wire:click="$set('selectedTeacherId', {{ $teacher->id }})"
                                                        @click="openTeacher = false"
                                                        class="w-full flex items-center gap-3 px-4 py-3 text-right hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors {{ $selectedTeacherId == $teacher->id ? 'bg-indigo-50 dark:bg-indigo-900/30' : '' }}">
                                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-xs font-bold">
                                                            {{ mb_substr($teacher->full_name ?? '؟', 0, 1) }}
                                                        </div>
                                                        <span class="flex-1 text-sm font-medium text-gray-700 dark:text-gray-200">{{ $teacher->full_name }}</span>
                                                        @if($selectedTeacherId == $teacher->id)
                                                            <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                            </svg>
                                                        @endif
                                                    </button>
                                                @empty
                                                    <div class="px-4 py-6 text-center text-gray-500">لا يوجد معلمون</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                    @error('selectedTeacherId') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Subject Dropdown -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        <svg class="w-4 h-4 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                        المادة
                                    </label>
                                    <div class="relative" @click.away="openSubject = false">
                                        <button type="button" @click="openSubject = !openSubject"
                                            class="w-full flex items-center justify-between gap-2 px-4 py-3 text-right bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                                            <span class="flex-1 truncate text-sm">
                                                @if($selectedSubjectId)
                                                    @php $subject = $this->subjects->firstWhere('id', $selectedSubjectId); @endphp
                                                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ $subject?->name ?? 'مادة' }}</span>
                                                @else
                                                    <span class="text-gray-400">-- اختر المادة --</span>
                                                @endif
                                            </span>
                                            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{'rotate-180': openSubject}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>

                                        <div x-show="openSubject" x-transition class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl overflow-hidden">
                                            <div class="p-3 border-b border-gray-100 dark:border-gray-700">
                                                <input type="text" wire:model.live.debounce.300ms="searchSubject" placeholder="ابحث عن مادة..."
                                                    class="w-full px-4 py-2 text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500">
                                            </div>
                                            <div class="max-h-48 overflow-y-auto">
                                                @forelse($this->subjects as $subject)
                                                    <button type="button"
                                                        wire:click="$set('selectedSubjectId', {{ $subject->id }})"
                                                        @click="openSubject = false"
                                                        class="w-full flex items-center gap-3 px-4 py-3 text-right hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors {{ $selectedSubjectId == $subject->id ? 'bg-indigo-50 dark:bg-indigo-900/30' : '' }}">
                                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center">
                                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                            </svg>
                                                        </div>
                                                        <span class="flex-1 text-sm font-medium text-gray-700 dark:text-gray-200">{{ $subject->name }}</span>
                                                        @if($selectedSubjectId == $subject->id)
                                                            <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                            </svg>
                                                        @endif
                                                    </button>
                                                @empty
                                                    <div class="px-4 py-6 text-center text-gray-500">لا توجد مواد</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                    @error('selectedSubjectId') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Actions -->
                            @if($this->isReadOnly)
                                <div class="mt-6">
                                    <div class="mb-4 p-3 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 rounded-lg text-sm flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        هذا الجدول للقراءة فقط لأنه يتبع لسنة سابقة.
                                    </div>
                                    <button wire:click="closeModal"
                                        class="w-full py-3 px-4 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-medium transition-colors">
                                        إغلاق
                                    </button>
                                </div>
                            @else
                                <div class="mt-6 flex gap-3">
                                    <button wire:click="closeModal"
                                        class="flex-1 py-3 px-4 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        إلغاء
                                    </button>
                                    @if($conflictWarning && !$forceAdd)
                                        <button wire:click="confirmConflict"
                                            class="flex-1 py-3 px-4 rounded-xl bg-gradient-to-l from-amber-500 to-orange-500 text-white font-bold hover:from-amber-600 hover:to-orange-600 shadow-lg transition-all">
                                            استمرار رغم التعارض
                                        </button>
                                    @else
                                        <button wire:click="saveSession"
                                            class="flex-1 py-3 px-4 rounded-xl bg-gradient-to-l from-indigo-600 to-purple-600 text-white font-bold hover:from-indigo-700 hover:to-purple-700 shadow-lg transition-all">
                                            <span wire:loading.remove wire:target="saveSession">حفظ</span>
                                            <span wire:loading wire:target="saveSession">جاري الحفظ...</span>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

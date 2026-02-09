<div class="min-h-screen bg-slate-50/50 pb-12 font-sans">
    <!-- Premium Header with Glassmorphism -->
    <div class="relative h-64 bg-gradient-to-br from-indigo-900 via-slate-900 to-slate-900 overflow-hidden">
        <!-- Abstract Shapes -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl -mr-20 -mt-20 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-72 h-72 bg-blue-500/20 rounded-full blur-3xl -ml-20 -mb-20 animate-pulse" style="animation-delay: 2s"></div>
        
        <!-- Pattern Overlay -->
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:20px_20px]"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-full flex flex-col justify-center">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white/10 backdrop-blur-md rounded-lg border border-white/10">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">ملف ولي الأمر</h1>
                </div>
                <a href="{{ route('guardians.index') }}" wire:navigate class="group flex items-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/10 rounded-full text-sm font-medium text-white transition-all duration-300">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                    <span>عودة للقائمة</span>
                </a>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 -mt-20 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Guardian Profile Card -->
            <div class="lg:col-span-4">
                <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 overflow-hidden border border-slate-100 sticky top-6">
                    <!-- Profile Header -->
                    <div class="relative pt-12 pb-6 px-6 text-center bg-gradient-to-b from-slate-50 to-white">
                        <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-b from-indigo-50/50 to-transparent"></div>
                        
                        <div class="relative w-28 h-28 mx-auto mb-4">
                            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full blur-lg opacity-30"></div>
                            <div class="relative w-full h-full bg-white rounded-full p-1 shadow-lg">
                                <div class="w-full h-full bg-gradient-to-br from-slate-100 to-slate-200 rounded-full flex items-center justify-center text-3xl font-bold text-slate-600">
                                    {{ substr($guardian->first_name, 0, 1) }}
                                </div>
                            </div>
                            <div class="absolute bottom-1 right-1 w-6 h-6 bg-emerald-500 border-4 border-white rounded-full" title="نشط"></div>
                        </div>

                        <h2 class="text-xl font-bold text-slate-900 mb-1">{{ $guardian->first_name }} {{ $guardian->last_name }}</h2>
                        <p class="text-sm text-slate-500 font-medium bg-slate-100 inline-block px-3 py-1 rounded-full">{{ $guardian->email }}</p>
                    </div>
                    
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 border-y border-slate-100 divide-x divide-x-reverse divide-slate-100 bg-slate-50/50">
                        <div class="p-4 text-center">
                            <span class="block text-2xl font-bold text-indigo-600">{{ $guardian->students->count() }}</span>
                            <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">الطلاب</span>
                        </div>
                        <div class="p-4 text-center">
                            <span class="block text-2xl font-bold text-slate-700">{{ $guardian->nationality_id ?? '--' }}</span>
                            <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">الجنسية</span>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="p-6 space-y-5">
                        <div class="flex items-start gap-4 group">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-0.5">رقم الهاتف</span>
                                <span class="text-sm font-semibold text-slate-700 font-mono">{{ $guardian->phone }}</span>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4 group">
                            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-0.5">جهة العمل</span>
                                <span class="text-sm font-semibold text-slate-700">{{ $guardian->employer ?? 'غير محدد' }}</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 group">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-0.5">العنوان</span>
                                <span class="text-sm font-semibold text-slate-700 leading-relaxed">{{ $guardian->address ?? 'لا يوجد عنوان مسجل' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4 bg-slate-50 border-t border-slate-100">
                        <button class="w-full py-2.5 px-4 bg-white border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 transition-all duration-300 shadow-sm hover:shadow">
                            تعديل البيانات
                        </button>
                    </div>
                </div>
            </div>

            <!-- Students Section -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- Section Header -->
                <div class="flex items-center justify-between bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">الطلاب المرتبطين</h3>
                        <p class="text-sm text-slate-500">إدارة الطلاب المرتبطين بولي الأمر</p>
                    </div>
                    <button wire:click="openLinkModal" class="group relative inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-100 transition-all duration-300 shadow-lg shadow-indigo-200 overflow-hidden">
                        <span class="relative z-10 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            ربط طالب جديد
                        </span>
                        <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-purple-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </button>
                </div>

                <!-- Students Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    @forelse($guardian->students as $student)
                        <div class="group relative bg-white rounded-2xl p-5 shadow-sm border border-slate-100 hover:shadow-xl hover:shadow-slate-200/50 hover:border-indigo-100 transition-all duration-300">
                            
                            <!-- Status Indicator -->
                            <div class="absolute top-5 left-5">
                                <span class="flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                </span>
                            </div>

                            <div class="flex items-start gap-4 mb-5">
                                <div class="relative">
                                    <img src="{{ $student->profile_photo_url }}" alt="{{ $student->full_name_ar }}" class="w-16 h-16 rounded-2xl object-cover shadow-md group-hover:scale-105 transition-transform duration-300">
                                    <div class="absolute -bottom-2 -right-2 bg-white rounded-lg p-1 shadow-sm border border-slate-100">
                                        <div class="w-6 h-6 bg-indigo-50 rounded flex items-center justify-center text-xs font-bold text-indigo-600">
                                            {{ substr($student->currentGrade?->name ?? '?', 0, 1) }}
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-slate-900 mb-1 group-hover:text-indigo-600 transition-colors">{{ $student->full_name_ar }}</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                            {{ $student->admission_number }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            {{ __('relationship.' . $student->pivot->relationship) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Badges -->
                            <div class="flex flex-wrap gap-2 mb-5">
                                @if($student->pivot->is_financial_sponsor)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        مسؤول مالي
                                    </span>
                                @endif
                                @if($student->pivot->is_emergency_contact)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-100">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        طوارئ
                                    </span>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                                <a href="{{ route('students.show', $student->id) }}" class="text-sm font-medium text-slate-600 hover:text-indigo-600 transition-colors">
                                    عرض الملف الشخصي &larr;
                                </a>
                                <button wire:click="unlinkStudent({{ $student->id }})" 
                                        wire:confirm="هل أنت متأكد من فك ارتباط هذا الطالب؟"
                                        class="group/btn flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 hover:bg-red-50 transition-all duration-200">
                                    <svg class="w-4 h-4 transition-transform group-hover/btn:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    فك الارتباط
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full">
                            <div class="flex flex-col items-center justify-center py-16 px-4 bg-white rounded-2xl border-2 border-dashed border-slate-200 text-center">
                                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4 shadow-inner">
                                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-slate-900 mb-2">لا يوجد طلاب مرتبطين</h3>
                                <p class="text-slate-500 max-w-sm mx-auto mb-6">لم يتم ربط أي طلاب بولي الأمر هذا حتى الآن. ابدأ بربط الطلاب لإدارتهم ومتابعتهم.</p>
                                <button wire:click="openLinkModal" class="px-6 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:border-indigo-300 hover:text-indigo-600 hover:shadow-md transition-all duration-300">
                                    + ربط طالب جديد
                                </button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Premium Modal -->
    <x-modal name="link-student" focusable maxWidth="lg">
        <div class="bg-white p-0 overflow-hidden rounded-2xl">
            <!-- Modal Header -->
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900">ربط طالب جديد</h2>
                <button x-on:click="$dispatch('close-modal', 'link-student')" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-6">
                <!-- Search Input -->
                <div class="relative">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">البحث عن طالب</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="searchQuery" 
                               class="block w-full pr-10 bg-slate-50 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 sm:text-sm py-2.5" 
                               placeholder="ابحث بالاسم أو الرقم الأكاديمي...">
                    </div>

                    <!-- Search Results Dropdown -->
                    @if(!empty($this->searchResults))
                        <div class="absolute z-50 mt-2 w-full bg-white border border-slate-100 rounded-xl shadow-xl max-h-60 overflow-y-auto divide-y divide-slate-50">
                            @foreach($this->searchResults as $result)
                                <button wire:click="selectStudent({{ $result->id }})" 
                                        class="w-full text-right px-4 py-3 hover:bg-slate-50 transition-colors flex items-center justify-between group {{ $selectedStudentId === $result->id ? 'bg-indigo-50/50' : '' }}">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">
                                            {{ substr($result->first_name_ar, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ $result->full_name_ar }}</div>
                                            <div class="text-xs text-slate-500 font-mono">{{ $result->admission_number }}</div>
                                        </div>
                                    </div>
                                    @if($selectedStudentId === $result->id)
                                        <div class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if($selectedStudentId)
                    <div class="space-y-5 animate-fade-in-up">
                        <!-- Relationship Select -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">صلة القرابة</label>
                            <select wire:model="relationship" class="block w-full bg-slate-50 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 sm:text-sm py-2.5">
                                <option value="father">أب</option>
                                <option value="mother">أم</option>
                                <option value="brother">أخ</option>
                                <option value="uncle">عم/خال</option>
                                <option value="other">آخر</option>
                            </select>
                        </div>

                        <!-- Toggles Grid -->
                        <div class="grid grid-cols-1 gap-3">
                            <label class="flex items-center gap-4 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 hover:border-indigo-200 transition-all duration-200 group {{ $isFinancialSponsor ? 'bg-indigo-50/30 border-indigo-200' : '' }}">
                                <div class="relative flex items-center">
                                    <input type="checkbox" wire:model="isFinancialSponsor" class="peer sr-only">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                </div>
                                <div class="flex-1">
                                    <span class="block text-sm font-semibold text-slate-900 group-hover:text-indigo-700">المسؤول المالي</span>
                                    <span class="block text-xs text-slate-500">هذا الشخص هو المسؤول عن دفع الرسوم الدراسية</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-4 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 hover:border-indigo-200 transition-all duration-200 group {{ $isEmergencyContact ? 'bg-indigo-50/30 border-indigo-200' : '' }}">
                                <div class="relative flex items-center">
                                    <input type="checkbox" wire:model="isEmergencyContact" class="peer sr-only">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                </div>
                                <div class="flex-1">
                                    <span class="block text-sm font-semibold text-slate-900 group-hover:text-indigo-700">جهة اتصال للطوارئ</span>
                                    <span class="block text-xs text-slate-500">يمكن الاتصال به في الحالات الطارئة</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-4 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 hover:border-indigo-200 transition-all duration-200 group {{ $livesWith ? 'bg-indigo-50/30 border-indigo-200' : '' }}">
                                <div class="relative flex items-center">
                                    <input type="checkbox" wire:model="livesWith" class="peer sr-only">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                </div>
                                <div class="flex-1">
                                    <span class="block text-sm font-semibold text-slate-900 group-hover:text-indigo-700">يسكن معه</span>
                                    <span class="block text-xs text-slate-500">الطالب يقيم في نفس المنزل</span>
                                </div>
                            </label>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex justify-end gap-3">
                <button x-on:click="$dispatch('close-modal', 'link-student')" class="px-5 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors shadow-sm">
                    إلغاء
                </button>
                <button wire:click="linkStudent" 
                        @if(!$selectedStudentId) disabled @endif
                        class="px-5 py-2.5 bg-indigo-600 border border-transparent rounded-xl text-sm font-semibold text-white hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-100 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-indigo-200">
                    ربط الطالب
                </button>
            </div>
        </div>
    </x-modal>
</div>

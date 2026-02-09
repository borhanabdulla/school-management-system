<div class="space-y-6">
    {{-- Profile Card --}}
    <div class="bg-white shadow-lg rounded-2xl overflow-hidden">
        {{-- Header with Avatar --}}
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-8">
            <div class="flex items-center gap-6">
                {{-- Avatar --}}
                <div class="flex-shrink-0">
                    @if($teacher->staff->user->profile_photo_path ?? null)
                        <img src="{{ asset('storage/' . $teacher->staff->user->profile_photo_path) }}" 
                             alt="{{ $teacher->staff->full_name }}" 
                             class="h-24 w-24 rounded-full object-cover border-4 border-white/30 shadow-lg">
                    @else
                        <div class="h-24 w-24 rounded-full bg-white/20 flex items-center justify-center text-white text-3xl font-bold border-4 border-white/30">
                            {{ mb_substr($teacher->staff->first_name ?? 'M', 0, 1) }}
                        </div>
                    @endif
                </div>
                {{-- Name & Info --}}
                <div>
                    <h2 class="text-2xl font-bold text-white">{{ $teacher->staff->first_name }} {{ $teacher->staff->last_name }}</h2>
                    <p class="text-indigo-100 mt-1">{{ $teacher->specialization ?? 'تخصص غير محدد' }}</p>
                    <div class="flex items-center gap-4 mt-3">
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-white/20 text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            منذ {{ \Carbon\Carbon::parse($teacher->staff->joining_date)->diffForHumans() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Details Grid --}}
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Contact Info --}}
                <div class="space-y-4">
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        معلومات التواصل
                    </h3>
                    <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                        <div class="flex items-center gap-3">
                            <span class="text-gray-500 text-sm w-24">البريد:</span>
                            <span class="text-gray-900">{{ $teacher->staff->user->email ?? 'غير متوفر' }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-gray-500 text-sm w-24">الهاتف:</span>
                            <span class="text-gray-900">{{ $teacher->staff->phone ?? 'غير متوفر' }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-gray-500 text-sm w-24">رقم الموظف:</span>
                            <span class="text-gray-900 font-mono">{{ $teacher->staff->employee_number }}</span>
                        </div>
                    </div>
                </div>

                {{-- Work Info --}}
                <div class="space-y-4">
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        معلومات العمل
                    </h3>
                    <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                        <div class="flex items-center gap-3">
                            <span class="text-gray-500 text-sm w-24">تاريخ التعيين:</span>
                            <span class="text-gray-900">{{ \Carbon\Carbon::parse($teacher->staff->joining_date)->format('Y/m/d') }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-gray-500 text-sm w-24">النصاب الأسبوعي:</span>
                            <span class="text-gray-900">{{ $teacher->max_weekly_classes }} حصة</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-gray-500 text-sm w-24">الحصص الحالية:</span>
                            <span class="text-gray-900">{{ $teacher->courseOfferings->count() }} حصة</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Course Offerings --}}
    @if($teacher->courseOfferings->count() > 0)
    <div class="bg-white shadow-lg rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                المواد والحصص الدراسية
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($teacher->courseOfferings as $offering)
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-4 border border-gray-200 hover:shadow-md transition">
                    <div class="font-medium text-gray-900">{{ $offering->subject->name ?? 'مادة غير محددة' }}</div>
                    <div class="text-sm text-gray-500 mt-1">{{ $offering->classSection->grade->name ?? '' }} - {{ $offering->classSection->name ?? '' }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Actions Card --}}
    <div class="bg-white shadow-lg rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
                إجراءات
            </h3>
        </div>
        <div class="p-6">
            <div class="flex flex-col sm:flex-row gap-4">
                {{-- Edit Button (placeholder) --}}
                <a href="#" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    تعديل البيانات
                </a>

                {{-- Delete Button --}}
                @if($canDelete)
                    <button wire:click="delete" 
                            wire:confirm="هل أنت متأكد من حذف هذا المعلم؟ سيتم حذف جميع البيانات المرتبطة به."
                            class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-medium transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        حذف المعلم
                    </button>
                @else
                    {{-- Disabled Delete with Tooltip --}}
                    <div class="relative group">
                        <button disabled 
                                class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-300 text-gray-500 rounded-xl font-medium cursor-not-allowed">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            حذف المعلم
                        </button>
                        {{-- Tooltip --}}
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 bg-gray-900 text-white text-sm rounded-lg p-3 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">
                            <div class="font-medium mb-1">لا يمكن الحذف:</div>
                            <ul class="list-disc list-inside text-gray-300 text-xs">
                                @foreach($deleteBlockers as $blocker)
                                    <li>{{ $blocker }}</li>
                                @endforeach
                            </ul>
                            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 w-2 h-2 bg-gray-900 rotate-45"></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

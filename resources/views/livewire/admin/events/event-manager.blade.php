<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Flash Messages -->
        <!-- Hero Header with Stats -->
        <div class="relative overflow-hidden bg-gradient-to-l from-indigo-600 via-purple-600 to-teal-500 rounded-3xl p-8 mb-8 shadow-2xl">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <defs>
                        <pattern id="calendar-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                            <rect x="0" y="0" width="4" height="4" fill="currentColor" opacity="0.3"/>
                        </pattern>
                    </defs>
                    <rect fill="url(#calendar-pattern)" width="100" height="100"/>
                </svg>
            </div>

            <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-black text-white">التقويم الأكاديمي</h1>
                            <p class="text-white/80 mt-1">إدارة العطل والمناسبات المدرسية</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="flex flex-wrap gap-4">
                    <div class="bg-white/20 backdrop-blur-sm rounded-2xl px-6 py-4 text-center min-w-[120px]">
                        <div class="text-3xl font-black text-white">{{ $events->count() }}</div>
                        <div class="text-white/80 text-sm">إجمالي الأحداث</div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-2xl px-6 py-4 text-center min-w-[120px]">
                        <div class="text-3xl font-black text-white">{{ $events->where('is_holiday', true)->count() }}</div>
                        <div class="text-white/80 text-sm">أيام العطل</div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-2xl px-6 py-4 text-center min-w-[120px]">
                        <div class="text-3xl font-black text-white">{{ $events->where('type', 'exam')->count() }}</div>
                        <div class="text-white/80 text-sm">فترات اختبارات</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">

            <!-- Main Content Area -->
            <div class="xl:col-span-8 space-y-6">

                <!-- Upcoming Events Timeline -->
                @php
                    $upcomingEvents = $events->where('start_date', '>=', now())->sortBy('start_date')->take(3);
                @endphp
                @if($upcomingEvents->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        الأحداث القادمة
                    </h3>
                    <div class="space-y-4">
                        @foreach($upcomingEvents as $event)
                        <div class="flex items-start gap-4 p-4 rounded-xl bg-gradient-to-l from-gray-50 to-white dark:from-gray-900/50 dark:to-gray-800 border border-gray-100 dark:border-gray-700 hover:shadow-lg transition-all duration-300">
                            <!-- Date Badge -->
                            <div class="flex-shrink-0 w-16 h-16 rounded-xl 
                                @if($event->type === 'holiday') bg-blue-100 dark:bg-blue-900/30
                                @elseif($event->type === 'emergency') bg-red-100 dark:bg-red-900/30
                                @elseif($event->type === 'exam') bg-orange-100 dark:bg-orange-900/30
                                @else bg-green-100 dark:bg-green-900/30
                                @endif
                                flex flex-col items-center justify-center">
                                <span class="text-2xl font-black 
                                    @if($event->type === 'holiday') text-blue-600 dark:text-blue-400
                                    @elseif($event->type === 'emergency') text-red-600 dark:text-red-400
                                    @elseif($event->type === 'exam') text-orange-600 dark:text-orange-400
                                    @else text-green-600 dark:text-green-400
                                    @endif">
                                    {{ $event->start_date->format('d') }}
                                </span>
                                <span class="text-xs 
                                    @if($event->type === 'holiday') text-blue-500 dark:text-blue-300
                                    @elseif($event->type === 'emergency') text-red-500 dark:text-red-300
                                    @elseif($event->type === 'exam') text-orange-500 dark:text-orange-300
                                    @else text-green-500 dark:text-green-300
                                    @endif">
                                    {{ $event->start_date->translatedFormat('M') }}
                                </span>
                            </div>
                            <!-- Event Info -->
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-900 dark:text-gray-100">{{ $event->title }}</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    @if($event->days_count > 1)
                                        {{ $event->start_date->format('Y/m/d') }} - {{ $event->end_date->format('Y/m/d') }}
                                        <span class="text-xs">({{ $event->days_count }} أيام)</span>
                                    @else
                                        {{ $event->start_date->format('Y/m/d') }}
                                    @endif
                                </p>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        @if($event->type === 'holiday') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                                        @elseif($event->type === 'emergency') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                                        @elseif($event->type === 'exam') bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400
                                        @else bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                                        @endif">
                                        {{ $event->type_name }}
                                    </span>
                                    @if($event->is_holiday)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                            لا دوام
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <!-- Days Until -->
                            <div class="text-left">
                                @php $daysUntil = now()->diffInDays($event->start_date, false); @endphp
                                @if($daysUntil > 0)
                                    <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ $daysUntil }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">يوم متبقي</div>
                                @elseif($daysUntil == 0)
                                    <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">اليوم!</div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Events Table -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </span>
                            سجل جميع الأحداث
                        </h3>
                        <!-- Filter Badges -->
                        <div class="flex flex-wrap gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                عطلات: {{ $events->where('type', 'holiday')->count() }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">
                                اختبارات: {{ $events->where('type', 'exam')->count() }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                أنشطة: {{ $events->where('type', 'activity')->count() }}
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-right">
                            <thead class="bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-4 font-semibold">الحدث</th>
                                    <th class="px-6 py-4 font-semibold">الفترة</th>
                                    <th class="px-6 py-4 font-semibold">النوع</th>
                                    <th class="px-6 py-4 font-semibold">التأثير</th>
                                    <th class="px-6 py-4 font-semibold w-20"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse($events as $event)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl flex items-center justify-center
                                                    @if($event->type === 'holiday') bg-blue-100 dark:bg-blue-900/30
                                                    @elseif($event->type === 'emergency') bg-red-100 dark:bg-red-900/30
                                                    @elseif($event->type === 'exam') bg-orange-100 dark:bg-orange-900/30
                                                    @else bg-green-100 dark:bg-green-900/30
                                                    @endif">
                                                    @if($event->type === 'holiday')
                                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                                        </svg>
                                                    @elseif($event->type === 'emergency')
                                                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                        </svg>
                                                    @elseif($event->type === 'exam')
                                                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                    @else
                                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="font-bold text-gray-900 dark:text-gray-100">{{ $event->title }}</div>
                                                    @if($event->description)
                                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-1">{{ $event->description }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-gray-700 dark:text-gray-300">
                                                {{ $event->start_date->format('Y/m/d') }}
                                            </div>
                                            @if($event->days_count > 1)
                                                <div class="text-xs text-gray-400 mt-0.5">
                                                    → {{ $event->end_date->format('Y/m/d') }}
                                                    <span class="font-medium">({{ $event->days_count }} أيام)</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold
                                                @if($event->type === 'holiday') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                                                @elseif($event->type === 'emergency') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                                                @elseif($event->type === 'exam') bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400
                                                @else bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                                                @endif">
                                                {{ $event->type_name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($event->is_holiday)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                    </svg>
                                                    عطلة
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    دوام عادي
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <button wire:click="delete({{ $event->id }})"
                                                wire:confirm="هل أنت متأكد من حذف هذا الحدث؟"
                                                class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all opacity-0 group-hover:opacity-100">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-16 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-4">
                                                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                                <h4 class="text-lg font-bold text-gray-700 dark:text-gray-300 mb-1">لا توجد أحداث</h4>
                                                <p class="text-gray-500 dark:text-gray-400 text-sm">ابدأ بإضافة حدث جديد من النموذج</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar - Add Event Form -->
            <div class="xl:col-span-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 sticky top-6">
                    <!-- Form Header -->
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">إضافة حدث جديد</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">الأحداث تؤثر على الجدول والحضور</p>
                        </div>
                    </div>

                    <form wire:submit.prevent="save" class="space-y-5">
                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">عنوان الحدث</label>
                            <input type="text" wire:model="title" placeholder="مثال: اليوم الوطني"
                                class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm py-3">
                            @error('title') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Date Range -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">تاريخ البداية</label>
                                <input type="date" wire:model="start_date"
                                    class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 text-sm py-3">
                                @error('start_date') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">تاريخ النهاية</label>
                                <input type="date" wire:model="end_date"
                                    class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 text-sm py-3">
                                @error('end_date') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Event Type with Visual Selection -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">نوع الحدث</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="relative cursor-pointer">
                                    <input type="radio" wire:model="type" value="holiday" class="peer sr-only">
                                    <div class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-600 peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 transition-all">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                            </svg>
                                            <span class="font-semibold text-gray-700 dark:text-gray-300">عطلة رسمية</span>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" wire:model="type" value="emergency" class="peer sr-only">
                                    <div class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-600 peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-900/20 transition-all">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            <span class="font-semibold text-gray-700 dark:text-gray-300">طوارئ</span>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" wire:model="type" value="exam" class="peer sr-only">
                                    <div class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-600 peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20 transition-all">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span class="font-semibold text-gray-700 dark:text-gray-300">اختبارات</span>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" wire:model="type" value="activity" class="peer sr-only">
                                    <div class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-600 peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/20 transition-all">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="font-semibold text-gray-700 dark:text-gray-300">نشاط</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">الوصف (اختياري)</label>
                            <textarea wire:model="description" rows="2" placeholder="تفاصيل إضافية عن الحدث..."
                                class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 text-sm"></textarea>
                        </div>

                        <!-- Holiday Toggle -->
                        <div class="bg-gradient-to-l from-gray-50 to-white dark:from-gray-900/50 dark:to-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                            <label class="flex items-center justify-between cursor-pointer">
                                <div>
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">توقف الدراسة</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">لن يتم احتساب الغياب</p>
                                </div>
                                <div class="relative">
                                    <input type="checkbox" wire:model="is_holiday" class="sr-only peer">
                                    <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                </div>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" wire:loading.attr="disabled"
                            class="w-full flex justify-center items-center gap-2 py-4 px-4 rounded-xl text-base font-bold text-white bg-gradient-to-l from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-xl hover:shadow-2xl hover:shadow-indigo-500/25 transition-all duration-300 disabled:opacity-50">
                            <span wire:loading.remove class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                إضافة الحدث
                            </span>
                            <span wire:loading class="flex items-center gap-2">
                                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                جاري الحفظ...
                            </span>
                        </button>
                    </form>

                    <!-- Quick Tips -->
                    <div class="mt-6 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-xs text-amber-800 dark:text-amber-300">
                                <p class="font-semibold mb-1">تلميحات:</p>
                                <ul class="list-disc list-inside space-y-1 text-amber-700 dark:text-amber-400">
                                    <li>العطل تؤثر على حساب أيام الحضور</li>
                                    <li>الطوارئ ترسل إشعارات فورية</li>
                                    <li>يمكن تحديد فترات متعددة الأيام</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

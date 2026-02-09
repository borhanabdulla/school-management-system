{{--
    File: resources/views/components/academic/year-card.blade.php
--}}

@props([
    'year',
    'progress' => 0,
    'daysRemaining' => 0,
    'theme' => [],
    'anyActiveYearExists' => false 
])

@php
    $isPending = $year->status === \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Pending;
    $isActive = $year->status === \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Active;
    $isClosed = $year->status === \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Closed;
@endphp

<div class="group relative bg-surface rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 border border-border hover:-translate-y-1 z-0 hover:z-10">

    <!-- Status Bar -->
    <div class="absolute top-0 left-0 right-0 h-1.5 rounded-t-2xl {{ $theme['border_top'] ?? 'bg-gray-200' }}"></div>

    <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="text-2xl font-bold text-primary mb-1 {{ $theme['title_hover'] ?? '' }} transition-colors">
                    {{ $year->name }}
                </h3>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $theme['badge'] ?? 'bg-gray-100 text-gray-600' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $theme['dot'] ?? 'bg-gray-400' }} animate-pulse"></span>
                    {{ $year->status->label() }}
                </span>
            </div>
            
            <!-- أيقونة السنة -->
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-400 via-amber-500 to-yellow-600 text-white flex items-center justify-center font-bold text-lg shadow-lg transform group-hover:rotate-6 transition-transform">
                {{ substr($year->name, 2, 2) }}
            </div>
        </div>

        <!-- Date Info -->
        <div class="flex items-center justify-between text-sm text-secondary mb-6 bg-background p-3 rounded-xl border border-border">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-4 h-4 {{ $theme['icon_text'] ?? '' }}">
                    @include('icons.calendar')
                </span>
                <span>{{ \App\Infrastructure\Support\Helpers::formatDate($year->start_date) }}</span>
            </div>
            <div class="text-gray-300 dark:text-gray-600">➜</div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-4 h-4 {{ $theme['icon_text'] ?? '' }}">
                    @include('icons.calendar')
                </span>
                <span>{{ \App\Infrastructure\Support\Helpers::formatDate($year->end_date) }}</span>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="mb-6">
            <div class="flex justify-between text-xs font-medium mb-2">
                <span class="text-secondary">التقدم</span>
                <span class="{{ $theme['progress_text'] ?? '' }}">{{ round($progress) }}%</span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                <div class="{{ $theme['progress_bar'] ?? 'bg-primary' }} h-2.5 rounded-full transition-all duration-1000 ease-out"
                    style="width: {{ $progress }}%"></div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-3 gap-2 mb-6">
            <div class="text-center p-2 rounded-lg bg-background border border-border {{ $theme['hover_border'] ?? '' }} transition-colors">
                <div class="text-lg font-bold text-primary">{{ $year->terms_count }}</div>
                <div class="text-xs text-secondary">فصول</div>
            </div>
            <div class="text-center p-2 rounded-lg bg-background border border-border {{ $theme['hover_border'] ?? '' }} transition-colors">
                <div class="text-lg font-bold text-primary">{{ $year->students_count }}</div>
                <div class="text-xs text-secondary">طالب</div>
            </div>
            <div class="text-center p-2 rounded-lg bg-background border border-border {{ $theme['hover_border'] ?? '' }} transition-colors">
                <div class="text-lg font-bold {{ $daysRemaining < 0 ? 'text-red-500' : 'text-primary' }}">
                    {{ $daysRemaining < 0 ? 'منتهية' : $daysRemaining }}
                </div>
                <div class="text-xs text-secondary">{{ $daysRemaining < 0 ? '' : 'يوم متبقي' }}</div>
            </div>
        </div>

        {{-- Guidance Banner - توجيه المستخدم --}}
        @if ($isPending)
            @if ($year->terms_count < 2)
                <div class="mb-4 p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                    <div class="flex items-start gap-2">
                        <span class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5">@include('icons.information-circle')</span>
                        <div>
                            <p class="text-sm font-bold text-amber-800 dark:text-amber-200">الخطوة التالية: إضافة الفصول الدراسية</p>
                            <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">
                                أضف <strong>فصلين دراسيين</strong> على الأقل (الترم الأول والثاني) لتتمكن من تفعيل السنة.
                                <a href="{{ route('terms.index', ['year_id' => $year->id]) }}" class="underline font-bold hover:text-amber-900">اضغط هنا للإضافة ←</a>
                            </p>
                        </div>
                    </div>
                </div>
            @elseif (!$anyActiveYearExists)
                <div class="mb-4 p-3 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                    <div class="flex items-start gap-2">
                        <span class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5">@include('icons.check-circle')</span>
                        <div>
                            <p class="text-sm font-bold text-green-800 dark:text-green-200">جاهزة للتفعيل! ✅</p>
                            <p class="text-xs text-green-700 dark:text-green-300 mt-1">
                                يمكنك الآن <strong>تفعيل السنة</strong> من القائمة المنسدلة لبدء العام الدراسي.
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="mb-4 p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-start gap-2">
                        <span class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5">@include('icons.information-circle')</span>
                        <div>
                            <p class="text-sm font-bold text-blue-800 dark:text-blue-200">في انتظار إغلاق السنة الحالية</p>
                            <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">
                                يوجد سنة نشطة حالياً. أغلقها أولاً لتتمكن من تفعيل هذه السنة.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        @endif
        <!-- Actions -->
        <div class="flex items-center gap-3">
            <a href="{{ route('terms.index', ['year_id' => $year->id]) }}"
                class="flex-1 text-center py-2.5 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                إدارة الفصول
            </a>

            <!-- Dropdown Menu -->
            <div x-data="{ open: false }" @keydown.escape.window="open = false" class="relative">
                
                <button @click="open = !open"
                    class="p-2.5 rounded-xl border border-border hover:bg-gray-50 dark:hover:bg-gray-800 text-secondary transition-colors focus:outline-none focus:ring-2 focus:ring-primary/20 bg-surface">
                    <span class="inline-flex items-center justify-center w-5 h-5">
                        @include('icons.dots-vertical')
                    </span>
                </button>

                <div x-show="open" 
                     @click.away="open = false"
                     style="display: none;"
                     class="absolute top-full left-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-border overflow-hidden z-50 origin-top-left ring-1 ring-black/5"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="transform opacity-0 scale-95 -translate-y-2"
                     x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="transform opacity-0 scale-95 -translate-y-2">

                    <div class="py-1 flex flex-col divide-y divide-gray-100 dark:divide-gray-700">
                        
                        {{-- 1. قسم العرض والتعديل --}}
                        <div class="py-1">
                            <a href="{{ route('terms.index', ['year_id' => $year->id]) }}"
                                class="w-full text-right px-4 py-2.5 text-sm text-secondary hover:bg-primary/5 hover:text-primary flex items-center gap-2">
                                <span class="w-4 h-4 opacity-70">@include('icons.eye')</span>
                                عرض التفاصيل
                            </a>

                            @if ($year->canBeFullyEdited())
                                <button wire:click="edit({{ $year->id }})" @click="open = false"
                                    class="w-full text-right px-4 py-2.5 text-sm text-secondary hover:bg-primary/5 hover:text-primary flex items-center gap-2">
                                    <span class="w-4 h-4 opacity-70">@include('icons.pencil')</span>
                                    تعديل كامل
                                </button>
                            @elseif($year->canEditNameOnly())
                                <button wire:click="editName({{ $year->id }})" @click="open = false"
                                    class="w-full text-right px-4 py-2.5 text-sm text-secondary hover:bg-primary/5 hover:text-primary flex items-center gap-2">
                                    <span class="w-4 h-4 opacity-70">@include('icons.pencil')</span>
                                    تعديل الاسم
                                </button>
                            @endif
                        </div>

                        {{-- 2. قسم العمليات الحيوية (تفعيل / إغلاق) --}}
                        <div class="py-1">
                            @if ($isPending)
                                {{-- 
                                    التحسين هنا: فصلنا شرط الجاهزية عن شرط التفعيل 
                                    لنخبر المستخدم بالسبب الحقيقي 
                                --}}
                                @if ($year->isReadyForActivation())
                                    @if (!$anyActiveYearExists)
                                        {{-- الحالة المثالية: جاهزة ولا يوجد سنة نشطة --}}
                                        <button wire:click="activateYear({{ $year->id }})"
                                            onclick="return confirm('هل أنت متأكد من تفعيل هذه السنة الدراسية؟')"
                                            @click="open = false"
                                            class="w-full text-right px-4 py-2.5 text-sm text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 flex items-center gap-2 font-bold">
                                            <span class="w-4 h-4">@include('icons.check-circle')</span>
                                            تفعيل وبدء الدراسة
                                        </button>
                                    @else
                                        {{-- الحالة: جاهزة لكن النظام مشغول --}}
                                        <div class="px-4 py-2 text-xs text-gray-400 italic text-right bg-gray-50 dark:bg-gray-800/50 cursor-not-allowed select-none">
                                            <div class="flex items-center gap-1.5 mb-1 text-orange-500">
                                                <span class="w-3 h-3">@include('icons.lock')</span>
                                                <span class="font-bold">التفعيل معطل</span>
                                            </div>
                                            يوجد سنة نشطة حالياً، يجب إغلاقها أولاً.
                                        </div>
                                    @endif
                                @else
                                    {{-- الحالة: غير جاهزة (لا توجد فصول) --}}
                                    <div class="px-4 py-2 text-xs text-gray-400 italic text-right bg-red-50 dark:bg-red-900/10 cursor-not-allowed select-none">
                                        <div class="flex items-center gap-1.5 mb-1 text-red-500">
                                            <span class="w-3 h-3">@include('icons.plus')</span>
                                            <span class="font-bold">غير جاهزة للتفعيل</span>
                                        </div>
                                        يجب إضافة فصول دراسية أولاً.
                                    </div>
                                @endif

                            @elseif ($isActive)
                                <button wire:click="closeYear({{ $year->id }})"
                                    onclick="return confirm('هل أنت متأكد من إغلاق السنة الدراسية؟')"
                                    @click="open = false"
                                    class="w-full text-right px-4 py-2.5 text-sm text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-900/20 flex items-center gap-2 font-bold">
                                    <span class="w-4 h-4">@include('icons.archive')</span>
                                    إغلاق السنة
                                </button>
                            @endif
                        </div>

                        {{-- 3. قسم الإجراءات --}}
                        <div class="py-1">
                             @if ($isClosed)
                                <button wire:click="cloneYear({{ $year->id }})" @click="open = false"
                                    class="w-full text-right px-4 py-2.5 text-sm text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 flex items-center gap-2">
                                    <span class="w-4 h-4">@include('icons.copy')</span>
                                    نسخ لسنة جديدة
                                </button>
                             @endif

                            @if ($year->canBeDeleted())
                                <button wire:click="delete({{ $year->id }})"
                                    onclick="return confirm('هل أنت متأكد من حذف السنة {{ $year->name }}؟\n\nملاحظة: يمكن الحذف فقط إذا كانت السنة فارغة تماماً (لا طلاب، لا فصول، لا شعب، لا رسوم).')"
                                    @click="open = false"
                                    class="w-full text-right px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2">
                                    <span class="w-4 h-4">@include('icons.trash')</span>
                                    حذف
                                </button>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

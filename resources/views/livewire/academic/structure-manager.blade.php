<div class="min-h-screen bg-gradient-to-br from-background to-blue-50/30 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto space-y-8">
        {{-- 1. Header & Actions --}}
        <div
            class="relative overflow-hidden bg-gradient-to-r from-indigo-600 to-purple-700 rounded-2xl shadow-xl p-8 text-white">
            <div class="absolute top-0 right-0 w-32 h-32 bg-surface/10 rounded-full -translate-y-16 translate-x-16"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-surface/5 rounded-full translate-y-12 -translate-x-12"></div>

            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-surface/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 14l9-5-9-5-9 5 9 5zm0 0l9 5m-9 5l9-5m-9 5v6" />
                            </svg>
                        </div>
                        <h1 class="text-3xl font-bold">الهيكل التعليمي</h1>
                    </div>
                    <p class="text-indigo-100 max-w-md text-lg">إدارة المراحل الدراسية، الصفوف، ومسارات الترفيع الآلي
                        للنظام التعليمي</p>
                </div>

                <button wire:click="createStage"
                    class="group bg-surface/20 hover:bg-surface/30 backdrop-blur-sm text-white font-semibold py-3.5 px-7 rounded-xl shadow-lg flex items-center gap-3 transition-all duration-300 hover:scale-105 hover:shadow-2xl border border-surface/20">
                    <div class="p-1 bg-surface/20 rounded-lg group-hover:bg-surface/30 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <span>إضافة مرحلة جديدة</span>
                </button>
            </div>
        </div>

        {{-- Alerts --}}
        {{-- 2. Educational Ladder (Stages List) --}}
        <div class="space-y-8 relative">
            {{-- خط الربط الرأسي الخلفي (Vertical Connector) --}}
            <div
                class="absolute left-8 top-10 bottom-10 w-1 bg-gradient-to-b from-indigo-200 to-purple-200 hidden lg:block z-0 rounded-full">
            </div>

            @forelse($stages as $index => $stage)
                @php
                    // تحديد ثيم لوني لكل مرحلة بناءً على ترتيبها
                    $colors = [
                        0 => [
                            'bg' => 'bg-gradient-to-r from-emerald-500 to-teal-600',
                            'light_bg' => 'bg-emerald-50',
                            'border' => 'border-emerald-200',
                            'text' => 'text-emerald-800',
                            'icon_bg' => 'bg-emerald-100',
                            'icon_text' => 'text-emerald-600',
                            'btn_hover' => 'hover:bg-emerald-100',
                            'badge' => 'bg-emerald-500',
                        ],
                        1 => [
                            'bg' => 'bg-gradient-to-r from-blue-500 to-indigo-600',
                            'light_bg' => 'bg-blue-50',
                            'border' => 'border-blue-200',
                            'text' => 'text-blue-800',
                            'icon_bg' => 'bg-blue-100',
                            'icon_text' => 'text-blue-600',
                            'btn_hover' => 'hover:bg-blue-100',
                            'badge' => 'bg-blue-500',
                        ],
                        2 => [
                            'bg' => 'bg-gradient-to-r from-purple-500 to-fuchsia-600',
                            'light_bg' => 'bg-purple-50',
                            'border' => 'border-purple-200',
                            'text' => 'text-purple-800',
                            'icon_bg' => 'bg-purple-100',
                            'icon_text' => 'text-purple-600',
                            'btn_hover' => 'hover:bg-purple-100',
                            'badge' => 'bg-purple-500',
                        ],
                        3 => [
                            'bg' => 'bg-gradient-to-r from-amber-500 to-orange-600',
                            'light_bg' => 'bg-amber-50',
                            'border' => 'border-amber-200',
                            'text' => 'text-amber-800',
                            'icon_bg' => 'bg-amber-100',
                            'icon_text' => 'text-amber-600',
                            'btn_hover' => 'hover:bg-amber-100',
                            'badge' => 'bg-amber-500',
                        ],
                    ];
                    $theme = $colors[$index % 4];
                @endphp

                <div
                    class="relative z-10 pl-0 lg:pl-20 transition-all duration-300 hover:transform hover:scale-[1.005]">
                    {{-- أيقونة المرحلة على الخط الزمني --}}
                    <div
                        class="hidden lg:flex absolute left-4 top-8 -translate-x-1/2 w-10 h-10 rounded-full {{ $theme['bg'] }} border-4 border-white items-center justify-center text-white font-bold shadow-lg z-20">
                        {{ $index + 1 }}
                    </div>

                    <div
                        class="bg-surface rounded-2xl shadow-lg border border-border overflow-hidden transition-all duration-300 hover:shadow-xl">
                        {{-- Stage Header --}}
                        <div class="relative overflow-hidden">
                            <div class="absolute inset-0 {{ $theme['bg'] }} opacity-5"></div>
                            <div
                                class="relative px-6 py-5 border-b border-border flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-12 h-12 rounded-xl {{ $theme['icon_bg'] }} flex items-center justify-center {{ $theme['icon_text'] }} shadow-sm">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-foreground">{{ $stage->name }}</h3>
                                        <div class="flex flex-wrap items-center gap-2 mt-2">
                                            <span
                                                class="flex items-center gap-1.5 bg-surface px-3 py-1 rounded-lg text-sm font-medium text-muted-foreground shadow-sm border border-border">
                                                <svg class="w-4 h-4 text-muted-foreground" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                النجاح: {{ $stage->min_passing_percentage }}%
                                            </span>
                                            <span
                                                class="flex items-center gap-1.5 bg-surface px-3 py-1 rounded-lg text-sm font-medium text-muted-foreground shadow-sm border border-border">
                                                <svg class="w-4 h-4 text-muted-foreground" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                                {{ $stage->grading_system == 'gpa' ? 'معدل تراكمي' : 'درجات عادية' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 self-end sm:self-auto">
                                    <button wire:click="createGrade({{ $stage->id }})"
                                        class="group bg-surface border border-border text-foreground hover:text-foreground hover:border-border px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 shadow-sm flex items-center gap-2 hover:shadow-md">
                                        <div class="p-1 rounded-lg group-hover:bg-background transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                        </div>
                                        إضافة صف
                                    </button>

                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open" @click.away="open = false"
                                            class="p-2 text-muted-foreground hover:text-muted-foreground rounded-xl {{ $theme['btn_hover'] }} transition-all duration-200 hover:bg-opacity-50">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                            </svg>
                                        </button>
                                        <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            class="absolute left-0 mt-2 w-48 bg-surface rounded-xl shadow-lg border border-border z-10 py-1 overflow-hidden">
                                            <button wire:click="editStage({{ $stage->id }})"
                                                class="flex w-full items-center px-4 py-2.5 text-sm text-foreground hover:bg-background transition-colors">
                                                <svg class="w-4 h-4 ml-2 text-muted-foreground" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                                تعديل المرحلة
                                            </button>
                                            <button wire:click="deleteStage({{ $stage->id }})"
                                                wire:confirm="حذف المرحلة؟"
                                                class="flex w-full items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                                <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                حذف المرحلة
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Grades Flow --}}
                        <div class="p-6 bg-surface">
                            @if ($stage->grades->count() > 0)
                                <div class="flex flex-wrap items-center justify-center md:justify-start gap-6">
                                    @foreach ($stage->grades as $i => $grade)
                                        {{-- Grade Card --}}
                                        <div
                                            class="group relative bg-surface border border-border rounded-2xl p-5 min-w-[200px] shadow-sm hover:shadow-xl hover:border-indigo-300 transition-all duration-300 hover:-translate-y-1">
                                            <div
                                                class="absolute -top-2 -right-2 w-6 h-6 rounded-full {{ $theme['badge'] }} text-white flex items-center justify-center text-xs font-bold shadow-md">
                                                {{ $grade->level_order }}
                                            </div>

                                            <div class="flex justify-between items-start mb-4">
                                                <div
                                                    class="w-10 h-10 rounded-xl {{ $theme['light_bg'] }} text-foreground flex items-center justify-center font-bold text-sm shadow-sm">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                    </svg>
                                                </div>
                                                <div
                                                    class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                    <button wire:click="editGrade({{ $grade->id }})"
                                                        class="text-indigo-500 hover:text-indigo-700 p-1.5 rounded-lg hover:bg-indigo-50 transition-colors">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                        </svg>
                                                    </button>
                                                    <button wire:click="deleteGrade({{ $grade->id }})"
                                                        wire:confirm="حذف الصف؟"
                                                        class="text-red-500 hover:text-red-700 p-1.5 rounded-lg hover:bg-red-50 transition-colors">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <h4 class="font-bold text-foreground text-lg mb-2">{{ $grade->name }}</h4>

                                            @if ($grade->nextGrade)
                                                <div
                                                    class="text-sm text-muted-foreground flex items-center gap-2 mt-3 p-2 bg-background rounded-lg">
                                                    <span class="text-indigo-600 font-medium">يُرفّع إلى:</span>
                                                    <span
                                                        class="font-semibold text-foreground">{{ $grade->nextGrade->name }}</span>
                                                </div>
                                            @else
                                                <div
                                                    class="text-sm bg-amber-100 text-amber-800 px-3 py-2 rounded-lg mt-3 font-medium flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    🎓 نهاية المسار
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Arrow Connector --}}
                                        @if (!$loop->last)
                                            <div class="text-border hidden md:block">
                                                <svg class="w-8 h-8 rtl:rotate-180 transform group-hover:scale-110 transition-transform"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                                </svg>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div
                                    class="text-center py-12 border-2 border-dashed border-border rounded-2xl bg-background/50">
                                    <div
                                        class="w-16 h-16 bg-surface rounded-full flex items-center justify-center mx-auto mb-4 text-muted-foreground shadow-sm border border-border">
                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </div>
                                    <p class="text-muted-foreground text-base mb-3">لا توجد صفوف في هذه المرحلة بعد.</p>
                                    <button wire:click="createGrade({{ $stage->id }})"
                                        class="text-indigo-600 font-semibold text-sm hover:text-indigo-800 transition-colors flex items-center justify-center gap-2 mx-auto">
                                        <span>إضافة الصف الأول</span>
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-20 bg-surface rounded-2xl border-2 border-dashed border-border shadow-sm">
                    <div
                        class="w-24 h-24 bg-background rounded-full flex items-center justify-center mx-auto mb-6 text-muted-foreground">
                        <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground mb-3">النظام فارغ تماماً!</h3>
                    <p class="text-muted-foreground max-w-md mx-auto mb-8 text-lg">ابدأ ببناء الهيكل التعليمي لمدرستك بإضافة
                        المرحلة الأولى (مثلاً: المرحلة الابتدائية).</p>
                    <button wire:click="createStage"
                        class="bg-gradient-to-r from-indigo-600 to-purple-700 hover:from-indigo-700 hover:to-purple-800 text-white font-bold py-3.5 px-8 rounded-xl shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl flex items-center gap-3 mx-auto">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        إضافة مرحلة جديدة
                    </button>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Modals will remain the same with some styling improvements --}}
    {{-- Stage Modal --}}
    @if ($showStageModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-foreground/70 backdrop-blur-sm transition-opacity"
                    wire:click="$set('showStageModal', false)"></div>
                <div
                    class="inline-block align-bottom bg-surface rounded-2xl text-right overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form wire:submit.prevent="saveStage">
                        <div class="bg-surface px-6 pt-6 pb-4">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-2xl font-bold text-foreground">
                                    {{ $isEditing ? 'تعديل المرحلة' : 'مرحلة جديدة' }}
                                </h3>
                                <button type="button" wire:click="$set('showStageModal', false)"
                                    class="text-muted-foreground hover:text-muted-foreground p-1 rounded-full hover:bg-background transition-colors">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-sm font-semibold text-foreground mb-2">اسم المرحلة</label>
                                    <input type="text" wire:model="stageForm.name"
                                        placeholder="مثال: المرحلة الابتدائية"
                                        class="w-full rounded-xl border-border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3 px-4 border transition-colors">
                                    @error('stageForm.name')
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-foreground mb-2">الترتيب</label>
                                        <input type="number" wire:model="stageForm.rank"
                                            class="w-full rounded-xl border-border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3 px-4 border transition-colors">
                                        @error('stageForm.rank')
                                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-foreground mb-2">نسبة النجاح
                                            (%)</label>
                                        <input type="number" step="0.1"
                                            wire:model="stageForm.min_passing_percentage"
                                            class="w-full rounded-xl border-border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3 px-4 border transition-colors">
                                        @error('stageForm.min_passing_percentage')
                                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-background px-6 py-4 sm:flex sm:flex-row-reverse gap-3 rounded-b-2xl">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-3 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none transition-colors sm:w-auto sm:text-sm">
                                حفظ
                            </button>
                            <button type="button" wire:click="$set('showStageModal', false)"
                                class="mt-3 w-full inline-flex justify-center rounded-xl border border-border shadow-sm px-6 py-3 bg-surface text-base font-medium text-foreground hover:bg-background transition-colors sm:mt-0 sm:w-auto sm:text-sm">
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Grade Modal --}}
    @if ($showGradeModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-foreground/70 backdrop-blur-sm transition-opacity"
                    wire:click="$set('showGradeModal', false)"></div>
                <div
                    class="inline-block align-bottom bg-surface rounded-2xl text-right overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form wire:submit.prevent="saveGrade">
                        <div class="bg-surface px-6 pt-6 pb-4">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-2xl font-bold text-foreground">
                                    {{ $isEditing ? 'تعديل الصف' : 'صف دراسي جديد' }}
                                </h3>
                                <button type="button" wire:click="$set('showGradeModal', false)"
                                    class="text-muted-foreground hover:text-muted-foreground p-1 rounded-full hover:bg-background transition-colors">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-sm font-semibold text-foreground mb-2">المرحلة
                                        الدراسية</label>
                                    <select wire:model.live="gradeForm.educational_stage_id"
                                        class="w-full rounded-xl border-border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3 px-4 border transition-colors">
                                        <option value="">-- اختر المرحلة --</option>
                                        @foreach ($allStages as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('gradeForm.educational_stage_id')
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-foreground mb-2">اسم الصف</label>
                                    <input type="text" wire:model="gradeForm.name"
                                        placeholder="مثال: الأول الابتدائي"
                                        class="w-full rounded-xl border-border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3 px-4 border transition-colors">
                                    @error('gradeForm.name')
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-foreground mb-2">الترتيب
                                            بالمرحلة</label>
                                        <input type="number" wire:model="gradeForm.level_order"
                                            class="w-full rounded-xl border-border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3 px-4 border transition-colors">
                                        @error('gradeForm.level_order')
                                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-foreground mb-2">الصف التالي
                                            (الترفيع)</label>
                                        <select wire:model="gradeForm.next_grade_id"
                                            class="w-full rounded-xl border-border shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3 px-4 border transition-colors">
                                            <option value="">-- تخرج (نهاية المسار) --</option>
                                            @foreach ($allGrades as $g)
                                                <option value="{{ $g->id }}">{{ $g->stage->name }} -
                                                    {{ $g->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('gradeForm.next_grade_id')
                                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-background px-6 py-4 sm:flex sm:flex-row-reverse gap-3 rounded-b-2xl">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-3 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none transition-colors sm:w-auto sm:text-sm">
                                حفظ
                            </button>
                            <button type="button" wire:click="$set('showGradeModal', false)"
                                class="mt-3 w-full inline-flex justify-center rounded-xl border border-border shadow-sm px-6 py-3 bg-surface text-base font-medium text-foreground hover:bg-background transition-colors sm:mt-0 sm:w-auto sm:text-sm">
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

<div class="p-6 space-y-8" 
    x-data="{ 
        activeTab: @entangle('activeTab'), 
        showLibModal: false, 
        showAllocModal: false, 
        showCreateCurriculumModal: false,
        message: null,
        type: 'success'
    }"
    x-on:open-modal.window="
        const modal = Array.isArray($event.detail) ? $event.detail[0] : $event.detail;
        if(modal === 'lib-modal') showLibModal = true;
        if(modal === 'alloc-modal') showAllocModal = true;
        if(modal === 'create-curriculum-modal') showCreateCurriculumModal = true;
    "
    x-on:close-modal.window="
        const modal = Array.isArray($event.detail) ? $event.detail[0] : $event.detail;
        if(modal === 'lib-modal') showLibModal = false;
        if(modal === 'alloc-modal') showAllocModal = false;
        if(modal === 'create-curriculum-modal') showCreateCurriculumModal = false;
    "
    x-on:switch-tab.window="activeTab = $event.detail"
    x-on:notify.window="message = $event.detail; type = 'success'; setTimeout(() => message = null, 3000)"
    x-on:error.window="message = $event.detail; type = 'error'; setTimeout(() => message = null, 5000)"
>
    {{-- 1. Header & Navigation --}}
    <div
        class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-surface p-6 rounded-2xl shadow-sm border border-border">
        <div>
            <h2 class="text-2xl font-bold text-foreground">المواد والمناهج الدراسية</h2>
            <p class="text-sm text-muted-foreground mt-1">إدارة مكتبة المواد وبناء المناهج الدراسية لكل صف</p>
        </div>
        <div class="flex bg-background p-1 rounded-xl">
            <button @click="activeTab = 'library'"
                :class="activeTab === 'library' ? 'bg-surface text-blue-600 shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                class="px-6 py-2 rounded-lg text-sm font-bold transition-all">
                📚 مكتبة المواد
            </button>
            <button @click="activeTab = 'curriculum'"
                :class="activeTab === 'curriculum' ? 'bg-surface text-indigo-600 shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                class="px-6 py-2 rounded-lg text-sm font-bold transition-all">
                🏗️ بناء المناهج
            </button>
        </div>
    </div>

    {{-- Alerts (Alpine) --}}
    <div x-show="message" x-transition 
         class="fixed top-4 left-4 z-50 p-4 rounded-xl shadow-lg flex items-center gap-3"
         :class="type === 'success' ? 'bg-green-50 text-green-800 border-l-4 border-green-500' : 'bg-red-50 text-red-800 border-l-4 border-red-500'">
        <span x-text="type === 'success' ? '✓' : '⚠'" class="font-bold text-lg"></span>
        <span x-text="message" class="font-medium"></span>
        <button @click="message = null" class="ml-4 opacity-50 hover:opacity-100">&times;</button>
    </div>

    {{-- ================= TAB 1: LIBRARY ================= --}}
    <div x-show="activeTab === 'library'" class="space-y-6 animate-fade-in">
        {{-- Search & Actions --}}
        <div class="flex flex-col md:flex-row gap-4 justify-between">
            <div class="relative w-full md:w-96">
                <input wire:model.live.debounce.300ms="searchLib" type="text"
                    placeholder="بحث عن مادة (الاسم، الكود)..."
                    class="w-full bg-surface border-none rounded-xl shadow-sm pl-10 pr-4 py-3 focus:ring-2 focus:ring-blue-500/20">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            <button wire:click="createSubject"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-blue-500/30 flex items-center gap-2 transition hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                مادة جديدة
            </button>
        </div>

        {{-- Subjects Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($subjects as $subject)
                @php
                    // Helper logic moved to view for simplicity or keep in component if complex
                    // For now, assuming we can access helper method or replicate logic
                    $type = $subject->type;
                    $theme = match ($type) {
                        'theory' => ['color' => 'blue', 'icon' => '📖', 'label' => 'نظري'],
                        'practical' => ['color' => 'orange', 'icon' => '🔬', 'label' => 'عملي'],
                        'both' => ['color' => 'purple', 'icon' => '🧪', 'label' => 'نظري وعملي'],
                        default => ['color' => 'gray', 'icon' => '📘', 'label' => 'عام'],
                    };
                    $color = $theme['color'];
                @endphp
                <div
                    class="group relative bg-surface rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 border border-border overflow-hidden">
                    {{-- Card Header --}}
                    <div
                        class="bg-{{ $color }}-50 dark:bg-{{ $color }}-900/20 p-5 border-b border-{{ $color }}-100 dark:border-{{ $color }}-800/30 flex justify-between items-start">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-surface text-2xl flex items-center justify-center shadow-sm">
                                {{ $theme['icon'] }}
                            </div>
                            <div>
                                <h3 class="font-bold text-foreground line-clamp-1" title="{{ $subject->name }}">
                                    {{ $subject->name }}</h3>
                                <span
                                    class="text-xs font-medium text-{{ $color }}-600 dark:text-{{ $color }}-300 bg-surface dark:bg-surface/70 px-2 py-0.5 rounded-md inline-block mt-1">
                                    {{ $subject->code ?? 'بدون كود' }}
                                </span>
                            </div>
                        </div>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                class="text-{{ $color }}-400 dark:text-{{ $color }}-300 hover:text-{{ $color }}-600 dark:hover:text-{{ $color }}-200 p-1 rounded-full hover:bg-surface/50 transition">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" style="display: none;"
                                class="absolute left-0 mt-2 w-32 bg-surface rounded-xl shadow-lg border border-border z-10 py-1">
                                <button wire:click="editSubject({{ $subject->id }})" @click="open = false"
                                    class="flex w-full items-center px-4 py-2 text-sm text-foreground hover:bg-background">تعديل</button>
                                <button wire:click="deleteSubject({{ $subject->id }})" wire:confirm="حذف المادة؟" @click="open = false"
                                    class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30">حذف</button>
                            </div>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="p-5">
                        <div class="flex items-center justify-between text-sm text-muted-foreground mb-2">
                            <span>النوع:</span>
                            <span class="font-medium text-foreground">{{ $theme['label'] }}</span>
                        </div>
                        <div class="mt-4 pt-3 border-t border-border text-xs text-muted-foreground flex justify-between">
                            <span>تاريخ الإضافة: {{ $subject->created_at->format('Y-m-d') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="col-span-full text-center py-12 bg-surface rounded-2xl border border-dashed border-border">
                    <div
                        class="w-16 h-16 bg-background rounded-full flex items-center justify-center mx-auto mb-4 text-muted-foreground text-2xl">
                        📚</div>
                    <h3 class="text-lg font-bold text-foreground">المكتبة فارغة</h3>
                    <p class="text-muted-foreground text-sm mt-1">ابدأ بإضافة المواد الدراسية إلى النظام.</p>
                </div>
            @endforelse
        </div>
        <div class="mt-4">{{ $subjects->links() }}</div>
    </div>

    {{-- ================= TAB 2: CURRICULUM ================= --}}
    <div x-show="activeTab === 'curriculum'" class="flex flex-col lg:flex-row gap-8 animate-fade-in" style="display: none;">
        {{-- Sidebar: Grade Selector --}}
        <div class="w-full lg:w-1/4 space-y-4">
            <div class="bg-surface rounded-2xl shadow-sm border border-border p-4">
                <h3 class="font-bold text-foreground mb-4 px-2">اختر الصف الدراسي</h3>
                <div class="space-y-2 max-h-[600px] overflow-y-auto custom-scrollbar">
                    @foreach ($allGrades as $grade)
                        <button wire:click="openCurriculum({{ $grade->id }})"
                            class="w-full flex items-center justify-between p-3 rounded-xl transition-all {{ $selectedGradeId === $grade->id ? 'bg-indigo-50 text-indigo-700 border border-indigo-200 shadow-sm dark:bg-indigo-900/30 dark:text-indigo-200 dark:border-indigo-700/30' : 'hover:bg-background text-muted-foreground border border-transparent' }}">
                            <span class="font-medium">{{ $grade->name }}</span>
                            <span
                                class="text-xs bg-surface px-2 py-1 rounded-md border border-border text-muted-foreground">
                                {{ $grade->subjects_count }} مواد
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Main Content: Curriculum Builder --}}
        <div class="w-full lg:w-3/4">
            @if ($selectedGrade)
                <div class="space-y-6">
                    {{-- Curriculum Header --}}
                    <div class="bg-indigo-600 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
                        <div
                            class="absolute top-0 left-0 w-full h-full opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]">
                        </div>
                        <div
                            class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div>
                                <h2 class="text-2xl font-bold">منهج {{ $selectedGrade->name }}</h2>
                                <p class="text-indigo-100 text-sm mt-1">تخصيص المواد الدراسية وتوزيع الدرجات</p>
                            </div>
                            <button wire:click="openAllocModal"
                                class="bg-surface text-indigo-600 hover:bg-indigo-50 font-bold py-2 px-6 rounded-xl shadow-md transition flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                إضافة مادة للمنهج
                            </button>
                        </div>

                        {{-- Quick Stats --}}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-indigo-500/30">
                            <div class="text-center">
                                <span
                                    class="block text-2xl font-bold">{{ $curriculumStats['total_subjects'] }}</span>
                                <span class="text-xs text-indigo-200">إجمالي المواد</span>
                            </div>
                            <div class="text-center">
                                <span
                                    class="block text-2xl font-bold">{{ $curriculumStats['total_credits'] }}</span>
                                <span class="text-xs text-indigo-200">مجموع الحصص</span>
                            </div>
                            <div class="text-center">
                                <span class="block text-2xl font-bold">{{ $curriculumStats['theory_count'] }} /
                                    {{ $curriculumStats['practical_count'] }}</span>
                                <span class="text-xs text-indigo-200">نظري / عملي</span>
                            </div>
                        </div>
                    </div>

                    {{-- Curriculum Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @forelse($gradeSubjects as $subject)
                            @php
                                $type = $subject->type;
                                $theme = match ($type) {
                                    'theory' => ['color' => 'blue', 'icon' => '📖', 'label' => 'نظري'],
                                    'practical' => ['color' => 'orange', 'icon' => '🔬', 'label' => 'عملي'],
                                    'both' => ['color' => 'purple', 'icon' => '🧪', 'label' => 'نظري وعملي'],
                                    default => ['color' => 'gray', 'icon' => '📘', 'label' => 'عام'],
                                };
                                $pivot = $subject->pivot;
                            @endphp
                            <div
                                class="bg-surface rounded-xl shadow-sm border border-border hover:border-indigo-300 transition-all group">
                                <div class="p-5">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex items-center gap-3">
                                            <div class="text-2xl">{{ $theme['icon'] }}</div>
                                            <div>
                                                <h4 class="font-bold text-foreground">{{ $subject->name }}</h4>
                                                <span class="text-xs text-muted-foreground">{{ $subject->code }}</span>
                                            </div>
                                        </div>
                                        <div
                                            class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button
                                                wire:click="editAllocation({{ $pivot->id }}, {{ $subject->id }}, {{ $pivot->credit_hours }}, '{{ $pivot->term_type }}')"
                                                class="p-1 text-indigo-500 hover:bg-indigo-50 rounded"><svg
                                                    class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg></button>
                                            <button wire:click="deleteAllocation({{ $pivot->id }})"
                                                wire:confirm="إزالة المادة من المنهج؟"
                                                class="p-1 text-red-500 hover:bg-red-50 rounded"><svg
                                                    class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg></button>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <div class="flex justify-between text-sm border-b border-border pb-2">
                                            <span class="text-muted-foreground">عدد الحصص</span>
                                            <span
                                                class="font-bold text-foreground">{{ $pivot->credit_hours }}</span>
                                        </div>
                                        <div class="pt-1">
                                            <span
                                                class="block w-full text-center text-xs font-medium bg-background text-muted-foreground py-1 rounded-md">
                                                {{ match ($pivot->term_type) {'full_year' => 'طوال العام','term_1' => 'الفصل الأول','term_2' => 'الفصل الثاني','term_3' => 'الفصل الثالث'} }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div
                                class="col-span-full text-center py-12 bg-surface rounded-2xl border border-dashed border-border">
                                <div
                                    class="w-16 h-16 bg-indigo-50 dark:bg-indigo-900/30 rounded-full flex items-center justify-center mx-auto mb-4 text-indigo-400 dark:text-indigo-300 text-2xl">
                                    🏗️</div>
                                <h3 class="text-lg font-bold text-foreground">المنهج فارغ</h3>
                                <p class="text-muted-foreground text-sm mt-1">لم يتم تخصيص أي مواد لهذا الصف بعد.</p>
                                <button wire:click="openAllocModal"
                                    class="mt-4 text-indigo-600 font-bold text-sm hover:underline">إضافة أول
                                    مادة</button>
                            </div>
                        @endforelse
                    </div>
                </div>
            @else
                <div
                    class="flex flex-col items-center justify-center h-96 bg-surface rounded-2xl border border-dashed border-border text-center p-6">
                    <div class="w-20 h-20 bg-background rounded-full flex items-center justify-center mb-6 text-4xl">
                        👈</div>
                    <h3 class="text-xl font-bold text-foreground mb-2">اختر صفاً للبدء</h3>
                    <p class="text-muted-foreground max-w-sm">قم باختيار صف دراسي من القائمة الجانبية لعرض وتعديل المنهج
                        الدراسي الخاص به.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ================= MODALS ================= --}}

    {{-- 1. Library Modal (Create/Edit Subject) --}}
    <div x-show="showLibModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-foreground/50 backdrop-blur-sm transition-opacity"
                @click="showLibModal = false"></div>
            <div
                class="inline-block align-bottom bg-surface rounded-2xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form wire:submit.prevent="saveSubject">
                    <div class="bg-surface px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-xl font-bold text-foreground mb-4">
                            {{ $form->id ? 'تعديل مادة' : 'إضافة مادة جديدة' }}</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">اسم المادة</label>
                                <input type="text" wire:model="form.name"
                                    class="w-full rounded-xl border-border shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('form.name')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-1">كود المادة</label>
                                    <input type="text" wire:model="form.code" placeholder="MATH101"
                                        class="w-full rounded-xl border-border shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('form.code')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-1">النوع</label>
                                    <select wire:model="form.type"
                                        class="w-full rounded-xl border-border shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="theory">نظري 📖</option>
                                        <option value="practical">عملي 🔬</option>
                                        <option value="both">نظري وعملي 🧪</option>
                                    </select>
                                    @error('form.type')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-background px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:w-auto sm:text-sm">حفظ</button>
                        <button type="button" @click="showLibModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-xl border border-border shadow-sm px-4 py-2 bg-surface text-base font-medium text-foreground hover:bg-background sm:mt-0 sm:w-auto sm:text-sm">إلغاء</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. Allocation Modal (Assign to Grade) --}}
    <div x-show="showAllocModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-indigo-900/50 backdrop-blur-sm transition-opacity"
                @click="showAllocModal = false"></div>
            <div
                class="inline-block align-bottom bg-surface rounded-2xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form wire:submit.prevent="saveAllocation">
                    <div class="bg-surface px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-xl font-bold text-foreground mb-4">
                            {{ $editingPivotId ? 'تعديل تخصيص المادة' : 'إضافة مادة للمنهج' }}</h3>
                        <div class="space-y-4">
                            {{-- Subject Selection (Only if creating) --}}
                            @if (!$editingPivotId)
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-1">المادة</label>
                                    <select wire:model="alloc_subject_id"
                                        class="w-full rounded-xl border-border shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">-- اختر مادة من المكتبة --</option>
                                        @foreach ($availableSubjects as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }}
                                                ({{ $s->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('alloc_subject_id')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                            @else
                                <div class="p-3 bg-background rounded-xl border border-border text-center">
                                    <span
                                        class="font-bold text-foreground">{{ $availableSubjects->first()->name ?? 'المادة' }}</span>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-1">عدد الحصص</label>
                                    <input type="number" wire:model="credit_hours"
                                        class="w-full rounded-xl border-border shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('credit_hours')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-1">فترة
                                        التدريس</label>
                                    <select wire:model="term_type"
                                        class="w-full rounded-xl border-border shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="full_year">طوال العام</option>
                                        <option value="term_1">الفصل الأول</option>
                                        <option value="term_2">الفصل الثاني</option>
                                        <option value="term_3">الفصل الثالث</option>
                                    </select>
                                    @error('term_type')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-background px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:w-auto sm:text-sm">حفظ</button>
                        <button type="button" @click="showAllocModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-xl border border-border shadow-sm px-4 py-2 bg-surface text-base font-medium text-foreground hover:bg-background sm:mt-0 sm:w-auto sm:text-sm">إلغاء</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

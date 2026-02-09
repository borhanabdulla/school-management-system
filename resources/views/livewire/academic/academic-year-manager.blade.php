<div class="space-y-8">

    {{-- 
        تم استبدال الاستعلام المباشر بـ $this->anyActiveYearExists 
        التي تعتمد على الكاش في الـ Component
    --}}

    <!-- Header Section -->
    <x-academic.page-header title="السنوات الدراسية"
        description="إدارة التقويم الأكاديمي، الفصول الدراسية، وحالات تفعيل السنوات.">
        <x-slot:icon>
            <span class="inline-flex items-center justify-center w-8 h-8">
                @include('icons.calendar')
            </span>
        </x-slot:icon>
        <x-slot:actions>
            <x-ui.button wire:click="create" wire:loading.attr="disabled"
                class="bg-white dark:bg-gray-800 text-primary dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700 shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 border-none px-6 py-3 text-base font-bold">
                <span class="inline-flex items-center justify-center w-5 h-5 ml-2">
                    @include('icons.plus')
                </span>
                سنة دراسية جديدة
            </x-ui.button>
        </x-slot:actions>
    </x-academic.page-header>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total --}}
        <button wire:click="filterByStatus('all')" class="text-right focus:outline-none group">
            <x-academic.stat-card label="إجمالي السنوات" :value="$statistics['total']" color="blue"
                class="group-hover:ring-2 ring-primary/20 transition-all">
                <x-slot:icon>
                    <span class="inline-flex items-center justify-center w-6 h-6">@include('icons.folder')</span>
                </x-slot:icon>
            </x-academic.stat-card>
        </button>

        {{-- Active --}}
        <button wire:click="filterByStatus('active')" class="text-right focus:outline-none group">
            <x-academic.stat-card label="السنة الحالية (النشطة)" :value="$statistics['active']" color="green"
                class="group-hover:ring-2 ring-green-500/20 transition-all">
                <x-slot:icon>
                    <span
                        class="inline-flex items-center justify-center w-6 h-6 animate-pulse">@include('icons.check-circle')</span>
                </x-slot:icon>
            </x-academic.stat-card>
        </button>

        {{-- Pending --}}
        <button wire:click="filterByStatus('pending')" class="text-right focus:outline-none group">
            <x-academic.stat-card label="مسودات (قيد الإعداد)" :value="$statistics['pending']" color="yellow"
                class="group-hover:ring-2 ring-yellow-500/20 transition-all">
                <x-slot:icon>
                    <span class="inline-flex items-center justify-center w-6 h-6">@include('icons.clock')</span>
                </x-slot:icon>
            </x-academic.stat-card>
        </button>

        {{-- Closed --}}
        <button wire:click="filterByStatus('closed')" class="text-right focus:outline-none group">
            <x-academic.stat-card label="المغلقة والمؤرشفة" :value="$statistics['closed']" color="gray"
                class="group-hover:ring-2 ring-gray-500/20 transition-all">
                <x-slot:icon>
                    <span class="inline-flex items-center justify-center w-6 h-6">@include('icons.archive')</span>
                </x-slot:icon>
            </x-academic.stat-card>
        </button>
    </div>

    <!-- Toolbar -->
    <div
        class="bg-surface rounded-2xl shadow-lg p-4 border border-border flex flex-col lg:flex-row justify-between items-center gap-4">
        <!-- Search -->
        <div class="relative w-full lg:w-96 group">
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-secondary group-focus-within:text-primary transition-colors" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search"
                class="block w-full pr-10 pl-4 py-2.5 border border-border rounded-xl bg-background text-primary placeholder-secondary focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm"
                placeholder="بحث عن سنة دراسية...">
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto justify-end">
            <div class="flex bg-background p-1 rounded-xl border border-border">
                @foreach ([['key' => 'all', 'label' => 'الكل'], ['key' => 'active', 'label' => 'نشطة'], ['key' => 'pending', 'label' => 'مسودة'], ['key' => 'closed', 'label' => 'مغلقة']] as $filter)
                    <button wire:click="$set('filterStatus', '{{ $filter['key'] }}')"
                        class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 {{ $filterStatus === $filter['key'] ? 'bg-white dark:bg-surface shadow-sm text-primary font-bold' : 'text-secondary hover:text-primary hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                        {{ $filter['label'] }}
                    </button>
                @endforeach
            </div>

            {{-- Sorting --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false"
                    class="flex items-center gap-2 px-4 py-2.5 bg-background border border-border rounded-xl text-secondary hover:text-primary hover:border-primary transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
                    </svg>
                    <span class="text-sm font-medium">ترتيب</span>
                </button>
                <div x-show="open" x-cloak style="display: none;"
                    class="absolute left-0 mt-2 w-48 bg-surface rounded-xl shadow-xl border border-border z-50 py-1 origin-top-left">
                    <button wire:click="$set('sortBy', 'start_date_desc')" @click="open = false"
                        class="block w-full text-right px-4 py-2 text-sm hover:bg-primary/5 {{ $sortBy === 'start_date_desc' ? 'text-primary font-bold' : 'text-secondary' }}">الأحدث
                        أولاً</button>
                    <button wire:click="$set('sortBy', 'start_date_asc')" @click="open = false"
                        class="block w-full text-right px-4 py-2 text-sm hover:bg-primary/5 {{ $sortBy === 'start_date_asc' ? 'text-primary font-bold' : 'text-secondary' }}">الأقدم
                        أولاً</button>
                    <button wire:click="$set('sortBy', 'name_asc')" @click="open = false"
                        class="block w-full text-right px-4 py-2 text-sm hover:bg-primary/5 {{ $sortBy === 'name_asc' ? 'text-primary font-bold' : 'text-secondary' }}">أبجدياً</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 relative">
        {{-- Loading Overlay --}}
        <div wire:loading.flex wire:target="filterStatus, search, sortBy"
            class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 z-10 items-center justify-center rounded-2xl backdrop-blur-sm">
            <div class="animate-spin rounded-full h-10 w-10 border-4 border-primary border-t-transparent"></div>
        </div>

        @forelse($academicYears as $year)
            <x-academic.year-card :year="$year" :any-active-year-exists="$this->anyActiveYearExists && $year->status !== \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Active">
                 <x-slot:actions>
                    @if($year->canBeArchived())
                        <button wire:click="archive({{ $year->id }})" wire:confirm="هل أنت متأكد من أرشفة هذه السنة؟ لا يمكن التراجع عن هذا الإجراء." class="text-gray-500 hover:text-gray-700">
                            @include('icons.archive')
                        </button>
                    @elseif($year->canBeClosed())
                        <a href="{{ route('academic-years.close', $year->id) }}" class="text-amber-500 hover:text-amber-700" title="إغلاق السنة الدراسية">
                            @include('icons.lock')
                        </a>
                    @endif
                 </x-slot:actions>
            </x-academic.year-card>
        @empty
            <div class="col-span-full">
                <x-academic.empty-state title="لا توجد سنوات دراسية"
                    message="لم يتم العثور على بيانات تطابق بحثك. ابدأ بإضافة سنة جديدة.">
                    <x-slot:action>
                        <x-ui.button wire:click="create" class="bg-primary text-white hover:bg-primary/90">
                            إضافة سنة جديدة
                        </x-ui.button>
                    </x-slot:action>
                </x-academic.empty-state>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($academicYears->hasPages())
        <div class="mt-6">
            {{ $academicYears->links() }}
        </div>
    @endif

    {{-- ================= MODAL SECTION ================= --}}
    <x-ui.modal wire:model="showModal" maxWidth="2xl">
        {{-- Alpine Component for Wizard Logic --}}
        <div x-data="{ 
            step: @entangle('step'),
            validateStep1() {
                // Simple client-side check
                if (!$wire.form.name || !$wire.form.start_date || !$wire.form.end_date) {
                    return false;
                }
                // Date logic check
                let start = new Date($wire.form.start_date);
                let end = new Date($wire.form.end_date);
                if (end <= start) {
                    return false;
                }
                return true;
            }
        }">
            <div class="px-6 py-5 border-b border-border bg-background">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <h3 class="text-xl font-bold text-primary flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-lg bg-gradient-to-br from-primary to-accent flex items-center justify-center shadow-md">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        {{ $isEditing ? 'تعديل السنة الدراسية' : 'إعداد سنة دراسية جديدة' }}
                    </h3>

                    <!-- Wizard Steps Indicator -->
                    <div
                        class="flex items-center gap-3 bg-surface px-3 py-1.5 rounded-full border border-border shadow-sm">
                        <div class="flex items-center gap-2">
                            <span
                                class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold transition-colors duration-300"
                                :class="step >= 1 ? 'bg-primary text-white' : 'bg-gray-200 text-gray-500'">1</span>
                            <span class="text-xs font-medium"
                                :class="step >= 1 ? 'text-primary' : 'text-secondary'">البيانات</span>
                        </div>
                        <div class="w-8 h-0.5 rounded-full" :class="step >= 2 ? 'bg-primary' : 'bg-gray-200'"></div>
                        <div class="flex items-center gap-2">
                            <span
                                class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold transition-colors duration-300"
                                :class="step >= 2 ? 'bg-primary text-white' : 'bg-gray-200 text-gray-500'">2</span>
                            <span class="text-xs font-medium"
                                :class="step >= 2 ? 'text-primary' : 'text-secondary'">الفصول</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-6">
                <!-- Step 1: Basic Info -->
                @include('livewire.academic.partials.wizard-step-1')

                <!-- Step 2: Terms Structure -->
                @include('livewire.academic.partials.wizard-step-2')
            </div>

            <div class="px-6 py-4 bg-background flex justify-between gap-3 rounded-b-lg border-t border-border">
                <x-ui.button variant="secondary" wire:click="$set('showModal', false)"
                    class="hover:bg-secondary/20">إلغاء</x-ui.button>

                <div class="flex gap-2">
                    <template x-if="step > 1">
                        <x-ui.button variant="secondary" wire:click="previousStep" class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                            السابق
                        </x-ui.button>
                    </template>

                    <template x-if="step < 2">
                        <x-ui.button wire:click="nextStep"
                            class="bg-primary text-white hover:bg-primary/90 flex items-center gap-2"
                            x-bind:disabled="!validateStep1()" x-bind:class="{'opacity-50 cursor-not-allowed': !validateStep1()}">
                            التالي
                            <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </x-ui.button>
                    </template>

                    <template x-if="step >= 2">
                        <x-ui.button wire:click="save" wire:loading.attr="disabled"
                            class="bg-gradient-to-r from-primary to-accent hover:from-primary/90 hover:to-accent/90 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all text-white">
                            <span
                                wire:loading.remove>{{ $isEditing ? 'تحديث البيانات' : (($activateAfterSave && !$this->anyActiveYearExists) ? 'حفظ وتفعيل السنة' : 'حفظ كمسودة') }}</span>
                            <span wire:loading>جاري الحفظ...</span>
                        </x-ui.button>
                    </template>
                </div>
            </div>
        </div>
    </x-ui.modal>
</div>

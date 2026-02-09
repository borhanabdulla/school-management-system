<div x-data="{ 
    showModal: false, 
    showCloneModal: false, 
    isEditing: false
}" 
@open-modal.window="isEditing = $event.detail.isEditing; showModal = true"
@close-modal.window="showModal = false"
@close-clone-modal.window="showCloneModal = false"
class="p-6 space-y-8">

    {{-- 1. Header & Controls --}}
    <x-academic.page-header :title="__('الشعب الدراسية')" :description="__('إدارة وتوزيع الفصول الدراسية على المبنى المدرسي')" variant="soft" size="tiny">
        <x-slot:icon>
            <span class="inline-flex items-center justify-center w-8 h-8">
                @include('icons.home')
            </span>
        </x-slot:icon>
        <x-slot:actions>
            <button @click="showCloneModal = true"
                class="bg-surface text-foreground hover:bg-background font-semibold py-2 px-3 rounded-lg border border-border flex items-center gap-2 transition">
                @include('icons.copy', ['class' => 'w-5 h-5'])
                {{ __('نسخ الهيكل') }}
            </button>
            <button wire:click="create"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg shadow-blue-500/30 flex items-center gap-2 transition hover:-translate-y-0.5">
                <span class="inline-flex items-center justify-center w-5 h-5">
                    @include('icons.plus')
                </span>
                {{ __('شعبة جديدة') }}
            </button>
        </x-slot:actions>
    </x-academic.page-header>

    {{-- 2. Filters Bar --}}
    <x-academic.section-filters :allYears="$allYears" :allGrades="$allGrades" :filterYear="$filterYear" :filterGrade="$filterGrade"
        :search="$search" />

    {{-- Alerts --}}
    <div x-data="{ message: null, type: 'success' }" 
         x-on:notify.window="message = $event.detail; type = 'success'; setTimeout(() => message = null, 3000)"
         x-on:error.window="message = $event.detail; type = 'error'; setTimeout(() => message = null, 5000)"
         x-show="message" 
         x-transition
         class="fixed top-4 left-4 z-50 p-4 rounded-xl shadow-lg flex items-center gap-3"
         :class="type === 'success' ? 'bg-green-50 border-l-4 border-green-500 text-green-800' : 'bg-red-50 border-l-4 border-red-500 text-red-800'"
         style="display: none;">
        <span x-text="type === 'success' ? '✓' : '⚠'" class="font-bold text-lg"></span>
        <span x-text="message" class="font-medium"></span>
        <button @click="message = null" class="opacity-50 hover:opacity-100">&times;</button>
    </div>

    {{-- 3. Classroom Grid (Grouped by Grade) --}}
    <div class="space-y-8">
        @forelse($gradesWithSections as $grade)
            @if ($grade->sections->count() > 0 || empty($search))
                <x-academic.grade-section-group :grade="$grade" />
            @endif
        @empty
            <x-academic.empty-state :title="__('لا توجد صفوف دراسية!')"
                :message="__('يجب تعريف الصفوف الدراسية أولاً في صفحة الهيكل التعليمي قبل إضافة الشعب.')">
            </x-academic.empty-state>
        @endforelse
    </div>

    {{-- Create/Edit Modal --}}
    <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-foreground/50 backdrop-blur-sm" @click="showModal = false"></div>
            
            <div x-show="showModal" x-transition.scale
                class="inline-block align-bottom bg-surface rounded-2xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form wire:submit.prevent="save">
                    <div class="bg-surface px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="text-xl font-bold text-foreground" x-text="isEditing ? '{{ __('تعديل بيانات الشعبة') }}' : '{{ __('إضافة شعبة جديدة') }}'"></h3>
                            <button type="button" @click="showModal = false" class="text-muted-foreground hover:text-muted-foreground">
                                <span class="text-2xl">&times;</span>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-1">{{ __('السنة الدراسية') }}</label>
                                    <select wire:model.blur="form.academic_year_id"
                                        class="w-full rounded-xl border-border shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">{{ __('-- اختر السنة --') }}</option>
                                        @foreach ($allYears as $y)
                                            <option value="{{ $y->id }}">{{ $y->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.academic_year_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-1">{{ __('الصف الدراسي') }}</label>
                                    <select wire:model.blur="form.grade_id"
                                        class="w-full rounded-xl border-border shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">{{ __('-- اختر الصف --') }}</option>
                                        @foreach ($allGrades as $g)
                                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.grade_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Name --}}
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-1">{{ __('اسم الشعبة') }}</label>
                                <input type="text" wire:model.blur="form.name" placeholder="{{ __('مثال: أ، ب، 101') }}"
                                    class="w-full rounded-xl border-border shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('form.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- Capacity & Gender --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-1">{{ __('السعة القصوى') }}</label>
                                    <input type="number" wire:model.blur="form.max_capacity"
                                        class="w-full rounded-xl border-border shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('form.max_capacity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-foreground mb-1">{{ __('نوع الطلاب') }}</label>
                                    <select wire:model.blur="form.gender_type"
                                        class="w-full rounded-xl border-border shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        @foreach(\App\Domains\Academic\ClassSection\Enums\SectionGenderType::cases() as $type)
                                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Active Toggle --}}
                            <div class="flex items-center gap-2 pt-2">
                                <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" wire:model.blur="form.is_active" id="toggle"
                                        class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-surface border-4 appearance-none cursor-pointer" />
                                    <label for="toggle" class="toggle-label block overflow-hidden h-6 rounded-full bg-border cursor-pointer"></label>
                                </div>
                                <label for="toggle" class="text-sm text-foreground">{{ __('تفعيل الشعبة') }}</label>
                            </div>
                            <style>
                                .toggle-checkbox:checked { right: 0; border-color: #68D391; }
                                .toggle-checkbox:checked+.toggle-label { background-color: #68D391; }
                            </style>
                        </div>
                    </div>
                    <div class="bg-background px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:w-auto sm:text-sm">
                            {{ __('حفظ') }}
                        </button>
                        <button type="button" @click="showModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-xl border border-border shadow-sm px-4 py-2 bg-surface text-base font-medium text-foreground hover:bg-background sm:mt-0 sm:w-auto sm:text-sm">
                            {{ __('إلغاء') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Clone Modal --}}
    <div x-show="showCloneModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showCloneModal" x-transition.opacity class="fixed inset-0 bg-purple-900/50 backdrop-blur-sm" @click="showCloneModal = false"></div>
            
            <div x-show="showCloneModal" x-transition.scale
                class="inline-block align-bottom bg-surface rounded-2xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <div class="bg-surface px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center gap-3 mb-4 text-purple-700">
                        <div class="bg-purple-100 p-2 rounded-lg">
                            @include('icons.copy', ['class' => 'w-6 h-6'])
                        </div>
                        <h3 class="text-lg font-bold text-foreground">{{ __('نسخ هيكل الشعب') }}</h3>
                    </div>
                    <p class="text-sm text-muted-foreground mb-6">{{ __('هذه العملية ستقوم بنسخ جميع الشعب من سنة دراسية سابقة إلى السنة الجديدة، مما يوفر عليك عناء الإدخال اليدوي.') }}</p>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-foreground mb-1">{{ __('من السنة (المصدر)') }}</label>
                            <select wire:model.blur="source_year_id"
                                class="w-full rounded-xl border-border shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="">{{ __('اختر...') }}</option>
                                @foreach ($allYears as $y)
                                    <option value="{{ $y->id }}">{{ $y->name }}</option>
                                @endforeach
                            </select>
                            @error('source_year_id') <span class="text-red-500 text-xs block">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex justify-center">
                            <svg class="w-6 h-6 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-foreground mb-1">{{ __('إلى السنة (الهدف)') }}</label>
                            <select wire:model.blur="target_year_id"
                                class="w-full rounded-xl border-border shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                <option value="">{{ __('اختر...') }}</option>
                                @foreach ($allYears as $y)
                                    <option value="{{ $y->id }}">{{ $y->name }}</option>
                                @endforeach
                            </select>
                            @error('target_year_id') <span class="text-red-500 text-xs block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                <div class="bg-background px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                    <button wire:click="executeClone" type="button"
                        class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 sm:w-auto sm:text-sm">
                        {{ __('بدء النسخ') }}
                    </button>
                    <button type="button" @click="showCloneModal = false"
                        class="mt-3 w-full inline-flex justify-center rounded-xl border border-border shadow-sm px-4 py-2 bg-surface text-base font-medium text-foreground hover:bg-background sm:mt-0 sm:w-auto sm:text-sm">
                        {{ __('إلغاء') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

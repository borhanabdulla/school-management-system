<div x-show="step === 1" x-cloak x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform -translate-x-4"
    x-transition:enter-end="opacity-100 transform translate-x-0" class="space-y-6">
    <div>
        <label class="block text-sm font-bold text-secondary mb-2">اسم السنة الدراسية</label>
        <input type="text" wire:model="form.name" pattern="[0-9]{4}-[0-9]{4}"
            class="mt-1 block w-full rounded-lg border-border shadow-sm focus:border-primary focus:ring-2 focus:ring-primary sm:text-sm bg-surface text-primary placeholder-secondary transition-all"
            placeholder="مثال: 2025-2026">
        @error('form.name')
            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
        @enderror
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-bold text-secondary mb-2">تاريخ البدء</label>
            <input type="date" wire:model="form.start_date"
                class="mt-1 block w-full rounded-lg border-border shadow-sm focus:border-primary focus:ring-2 focus:ring-primary sm:text-sm bg-surface text-primary transition-all disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed"
                @if($isEditing && $editingYear && !$editingYear->canEditDates()) disabled @endif>
            @error('form.start_date')
                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-bold text-secondary mb-2">تاريخ الانتهاء</label>
            <input type="date" wire:model="form.end_date"
                class="mt-1 block w-full rounded-lg border-border shadow-sm focus:border-primary focus:ring-2 focus:ring-primary sm:text-sm bg-surface text-primary transition-all disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed"
                @if($isEditing && $editingYear && !$editingYear->canEditDates()) disabled @endif>
            @error('form.end_date')
                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
            @enderror
        </div>
    </div>

    @if ($isEditing && $editingYear)
        @if ($editingYear->canBeFullyEdited())
            <div class="bg-yellow-50 dark:bg-yellow-900/10 p-4 rounded-xl border border-yellow-200">
                <h4 class="text-sm font-bold text-yellow-800 dark:text-yellow-400 mb-2">تعديل الحالة
                    يدوياً
                </h4>
                <select wire:model="form.status"
                    class="block w-full rounded-lg border-yellow-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 sm:text-sm bg-white dark:bg-surface text-primary">
                    <option value="pending">قيد الإعداد</option>
                    <option value="active"
                        {{ $this->anyActiveYearExists && $editingYear->status !== \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Active ? 'disabled' : '' }}>
                        نشطة
                        {{ $this->anyActiveYearExists && $editingYear->status !== \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Active ? '(يوجد سنة نشطة بالفعل)' : '' }}
                    </option>
                    <option value="closed">مغلقة (للحفظ)</option>
                </select>
            </div>
        @endif
    @endif
</div>

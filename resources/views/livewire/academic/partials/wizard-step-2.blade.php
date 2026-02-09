<div x-show="step === 2" x-cloak style="display: none;"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-x-4"
    x-transition:enter-end="opacity-100 transform translate-x-0" class="space-y-6">

    <div class="flex justify-between items-center p-4 bg-primary/5 rounded-xl border border-primary/10">
        <div>
            <h4 class="font-bold text-primary">هيكل الفصول الدراسية</h4>
            <p class="text-xs text-secondary mt-1">قم بتعريف الفصول (ترم أول، ترم ثاني...)</p>
        </div>
        <div class="flex gap-2">
            @if(!$isEditing || ($editingYear && $editingYear->canEditStructure()))
            <button wire:click="cloneStructureFromPrevious" type="button"
                class="px-3 py-1.5 text-xs font-bold text-primary bg-white dark:bg-surface border border-primary/20 rounded-lg hover:bg-primary hover:text-white transition-all shadow-sm">
                نسخ من السابق
            </button>
            <button wire:click="addTerm" type="button"
                class="px-3 py-1.5 text-xs font-bold text-white bg-primary rounded-lg hover:bg-primary/90 transition-all shadow-md">
                + إضافة فصل
            </button>
            @endif
        </div>
    </div>

    <div class="space-y-3 max-h-[350px] overflow-y-auto pr-1 custom-scrollbar">
        @if (empty($form->terms))
            <div
                class="text-center py-8 text-secondary border-2 border-dashed border-border rounded-xl">
                <p>لا توجد فصول مضافة بعد.</p>
            </div>
        @else
            @foreach ($form->terms as $index => $term)
                <div
                    class="p-4 rounded-xl border border-border bg-surface hover:border-primary/30 transition-colors relative group">
                    @if(!$isEditing || ($editingYear && $editingYear->canEditStructure()))
                    <button wire:click="removeTerm({{ $index }})" type="button"
                        class="absolute top-3 left-3 text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-all p-1 hover:bg-red-50 rounded-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                    </button>
                    @endif
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <div class="md:col-span-4">
                            <label
                                class="text-[10px] font-bold text-secondary uppercase mb-1 block">الاسم</label>
                            <input type="text" wire:model="form.terms.{{ $index }}.name"
                                placeholder="مثال: الفصل الأول"
                                class="block w-full rounded-lg border-border text-sm py-1.5 bg-background focus:ring-1 focus:ring-primary disabled:bg-gray-100 disabled:text-gray-500"
                                @if($isEditing && $editingYear && !$editingYear->canEditStructure()) disabled @endif>
                            @error("form.terms.{$index}.name")
                                <span class="text-red-500 text-[10px]">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="md:col-span-4">
                            <label
                                class="text-[10px] font-bold text-secondary uppercase mb-1 block">يبدأ</label>
                            <input type="date"
                                wire:model="form.terms.{{ $index }}.start_date"
                                class="block w-full rounded-lg border-border text-sm py-1.5 bg-background focus:ring-1 focus:ring-primary disabled:bg-gray-100 disabled:text-gray-500"
                                @if($isEditing && $editingYear && !$editingYear->canEditStructure()) disabled @endif>
                            @error("form.terms.{$index}.start_date")
                                <span class="text-red-500 text-[10px]">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="md:col-span-4">
                            <label
                                class="text-[10px] font-bold text-secondary uppercase mb-1 block">ينتهي</label>
                            <input type="date"
                                wire:model="form.terms.{{ $index }}.end_date"
                                class="block w-full rounded-lg border-border text-sm py-1.5 bg-background focus:ring-1 focus:ring-primary disabled:bg-gray-100 disabled:text-gray-500"
                                @if($isEditing && $editingYear && !$editingYear->canEditStructure()) disabled @endif>
                            @error("form.terms.{$index}.end_date")
                                <span class="text-red-500 text-[10px]">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- Activate After Save Option (Create Mode Only) --}}
    @if (!$isEditing)
        <div
            class="mt-6 p-4 rounded-xl border transition-all duration-300 {{ $activateAfterSave ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }}">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" wire:model.live="activateAfterSave" @disabled($this->anyActiveYearExists)
                    class="mt-1 w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500 transition-all">
                <div class="flex-1">
                    <span
                        class="text-sm font-bold {{ $activateAfterSave ? 'text-green-800' : 'text-gray-700' }}">
                        اعتماد وتفعيل هذه السنة فوراً
                    </span>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                        لا يمكن تفعيل سنة جديدة إذا كانت هناك سنة نشطة حالياً.
                    </p>
                </div>
            </label>

            @if ($this->anyActiveYearExists)
                <div
                    class="mt-3 flex items-start gap-2 text-xs text-orange-600 bg-white/50 p-2 rounded-lg border border-orange-100">
                    <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>تنبيه: يوجد سنة نشطة حالياً، سيتم حفظ السنة الجديدة كمسودة.</span>
                </div>
            @endif
        </div>
    @endif

</div>

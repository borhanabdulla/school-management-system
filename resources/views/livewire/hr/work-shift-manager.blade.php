<div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-6">
    <div class="max-w-7xl mx-auto">
        
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                    <span class="w-10 h-10 bg-gradient-to-br from-violet-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    فترات الدوام
                </h1>
                <p class="text-slate-400 mt-2">تحديد أوقات العمل لفئات الموظفين المختلفة</p>
            </div>
            
            <button wire:click="openCreateModal" 
                    class="px-6 py-3 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-xl font-semibold hover:from-violet-700 hover:to-purple-700 transition-all shadow-lg shadow-violet-500/25 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                إضافة فترة دوام
            </button>
        </div>

        {{-- Flash Messages --}}
        {{-- Search --}}
        <div class="mb-6">
            <div class="relative max-w-md">
                <input type="text" 
                       wire:model.live.debounce.300ms="search"
                       placeholder="بحث عن وردية..."
                       class="w-full pl-10 pr-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-slate-400 focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
                <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        {{-- Shifts Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($shifts as $shift)
                <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6 hover:bg-white/10 transition-all">
                    {{-- Header --}}
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-white">{{ $shift->name }}</h3>
                            <span class="inline-block mt-1 px-2 py-0.5 text-xs rounded-full 
                                {{ $shift->season === 'summer' ? 'bg-orange-500/20 text-orange-400' : 
                                   ($shift->season === 'winter' ? 'bg-blue-500/20 text-blue-400' : 'bg-slate-500/20 text-slate-400') }}">
                                {{ $seasonOptions[$shift->season] ?? $shift->season }}
                            </span>
                        </div>
                        <button wire:click="toggleActive({{ $shift->id }})" 
                                class="w-10 h-6 rounded-full transition-colors {{ $shift->is_active ? 'bg-emerald-500' : 'bg-slate-600' }} relative">
                            <span class="absolute top-1 {{ $shift->is_active ? 'right-1' : 'left-1' }} w-4 h-4 bg-white rounded-full transition-all"></span>
                        </button>
                    </div>

                    {{-- Time --}}
                    <div class="flex items-center gap-3 mb-4 text-slate-300">
                        <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-mono">{{ $shift->schedule_display }}</span>
                        <span class="text-xs text-slate-500">(سماح {{ $shift->grace_period_minutes }} د)</span>
                    </div>

                    {{-- Working Days --}}
                    <div class="flex flex-wrap gap-1 mb-4">
                        @foreach($dayOptions as $key => $label)
                            <span class="px-2 py-1 text-xs rounded {{ in_array($key, $shift->working_days ?? []) ? 'bg-violet-500/20 text-violet-400' : 'bg-slate-700/50 text-slate-500' }}">
                                {{ $label }}
                            </span>
                        @endforeach
                    </div>

                    {{-- Special Flags --}}
                    @if($shift->works_on_holidays)
                        <div class="flex items-center gap-2 text-amber-400 text-sm mb-4">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            يعمل في العطل الرسمية
                        </div>
                    @endif

                    {{-- Staff Count --}}
                    <div class="flex items-center justify-between text-sm text-slate-400 mb-4 pt-4 border-t border-white/10">
                        <span>الموظفون:</span>
                        <span class="font-bold text-white">{{ $shift->staff()->count() }}</span>
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-2">
                        <button wire:click="openEditModal({{ $shift->id }})" 
                                class="flex-1 py-2 bg-white/5 hover:bg-white/10 text-white rounded-lg transition-colors text-sm">
                            تعديل
                        </button>
                        <button wire:click="delete({{ $shift->id }})" 
                                wire:confirm="هل أنت متأكد من حذف هذه الوردية؟"
                                class="px-4 py-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center">
                    <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-slate-400 text-lg">لا توجد ورديات</p>
                    <p class="text-slate-500 text-sm mt-1">ابدأ بإضافة وردية جديدة</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $shifts->links() }}
        </div>
    </div>

    {{-- Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data x-init="document.body.classList.add('overflow-hidden')" x-on:remove="document.body.classList.remove('overflow-hidden')">
            <div class="flex items-center justify-center min-h-screen px-4">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" wire:click="closeModal"></div>

                {{-- Modal Content --}}
                <div class="relative bg-slate-800 border border-white/10 rounded-2xl w-full max-w-lg p-6 shadow-2xl">
                    <h2 class="text-xl font-bold text-white mb-6">
                        {{ $editingId ? 'تعديل الوردية' : 'إضافة وردية جديدة' }}
                    </h2>

                    <form wire:submit="save" class="space-y-5">
                        {{-- Name --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">اسم الوردية</label>
                            <input type="text" wire:model="name" 
                                   class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500"
                                   placeholder="مثال: الدوام الصيفي - إداري">
                            @error('name') <span class="text-red-400 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Season --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">الموسم</label>
                            <select wire:model="season" 
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
                                @foreach($seasonOptions as $key => $label)
                                    <option value="{{ $key }}" class="bg-slate-800">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Times --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">وقت البداية</label>
                                <input type="time" wire:model="start_time" 
                                       class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">وقت الانتهاء</label>
                                <input type="time" wire:model="end_time" 
                                       class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
                                @error('end_time') <span class="text-red-400 text-sm mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Grace Period --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">فترة السماح (بالدقائق)</label>
                            <input type="number" wire:model="grace_period_minutes" min="0" max="60"
                                   class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500">
                        </div>

                        {{-- Working Days --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">أيام العمل</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($dayOptions as $key => $label)
                                    <label class="flex items-center gap-2 px-3 py-2 bg-white/5 border border-white/10 rounded-lg cursor-pointer hover:bg-white/10 transition-colors">
                                        <input type="checkbox" wire:model="working_days" value="{{ $key }}" 
                                               class="rounded border-white/20 bg-white/5 text-violet-500 focus:ring-violet-500">
                                        <span class="text-sm text-white">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('working_days') <span class="text-red-400 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Flags --}}
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="works_on_holidays" 
                                       class="rounded border-white/20 bg-white/5 text-violet-500 focus:ring-violet-500">
                                <span class="text-sm text-slate-300">يعمل في العطل الرسمية</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="is_active" 
                                       class="rounded border-white/20 bg-white/5 text-violet-500 focus:ring-violet-500">
                                <span class="text-sm text-slate-300">نشط</span>
                            </label>
                        </div>

                        {{-- Actions --}}
                        <div class="flex justify-end gap-3 pt-4 border-t border-white/10">
                            <button type="button" wire:click="closeModal" 
                                    class="px-6 py-3 bg-white/5 hover:bg-white/10 text-white rounded-xl transition-colors">
                                إلغاء
                            </button>
                            <button type="submit" 
                                    class="px-6 py-3 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-xl font-semibold hover:from-violet-700 hover:to-purple-700 transition-all">
                                {{ $editingId ? 'حفظ التغييرات' : 'إضافة الوردية' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

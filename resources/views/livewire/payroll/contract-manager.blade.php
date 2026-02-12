<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">إدارة العقود</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">إدارة عقود الموظفين والرواتب الأساسية والبدلات</p>
            </div>
            <button wire:click="create" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                عقد جديد
            </button>
        </div>

        {{-- Alerts --}}
        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" wire:model.live.debounce.300ms="search" 
                        placeholder="بحث بالاسم..."
                        class="w-full px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <select wire:model.live="statusFilter" class="px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">جميع الحالات</option>
                    <option value="active">نشط</option>
                    <option value="expired">منتهي</option>
                    <option value="draft">مسودة</option>
                </select>
            </div>
        </div>

        {{-- Contracts Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">الموظف</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">الفترة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">الراتب الأساسي</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">البدلات</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($contracts as $contract)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                        <span class="text-indigo-600 dark:text-indigo-400 font-medium">{{ mb_substr($contract->staff?->first_name ?? '?', 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $contract->staff?->full_name ?? 'غير محدد' }}</p>
                                        @if($contract->is_locked)
                                            <span class="text-xs text-orange-600 dark:text-orange-400">🔒 مقفل</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $contract->start_date->format('Y/m/d') }} - {{ $contract->end_date->format('Y/m/d') }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                {{ number_format($contract->basic_salary, 2) }} ر.س
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ number_format($contract->total_allowances, 2) }} ر.س
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                        'expired' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                        'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                        'terminated' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
                                    ];
                                    $statusLabels = [
                                        'active' => 'نشط',
                                        'expired' => 'منتهي',
                                        'draft' => 'مسودة',
                                        'terminated' => 'منهي',
                                    ];
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$contract->status->value] ?? $statusColors['draft'] }}">
                                    {{ $statusLabels[$contract->status->value] ?? $contract->status->value }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    @unless($contract->is_locked)
                                        <button wire:click="edit({{ $contract->id }})" class="p-2 text-gray-400 hover:text-indigo-600 transition-colors" title="تعديل">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button wire:click="delete({{ $contract->id }})" wire:confirm="هل أنت متأكد من حذف هذا العقد؟" class="p-2 text-gray-400 hover:text-red-600 transition-colors" title="حذف">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                لا توجد عقود بعد
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($contracts->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $contracts->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Contract Form Modal --}}
    <x-ui.modal wire:model="showForm" maxWidth="2xl">
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">
                {{ $editingId ? 'تعديل العقد' : 'عقد جديد' }}
            </h2>

            <form wire:submit="save" class="space-y-6">
                {{-- Staff Selection --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">الموظف</label>
                    <select wire:model="form.staff_id" class="w-full px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" {{ $editingId ? 'disabled' : '' }}>
                        <option value="">اختر الموظف...</option>
                        @foreach($staffList as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->full_name }}</option>
                        @endforeach
                    </select>
                    @error('form.staff_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Dates --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">تاريخ البداية</label>
                        <input type="date" wire:model="form.start_date" class="w-full px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('form.start_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">تاريخ النهاية</label>
                        <input type="date" wire:model="form.end_date" class="w-full px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('form.end_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Basic Salary --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">الراتب الأساسي</label>
                    <div class="relative">
                        <input type="number" step="0.01" wire:model="form.basic_salary" placeholder="0.00" class="w-full px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white pl-12">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">ر.س</span>
                    </div>
                    @error('form.basic_salary') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Items (Allowances & Deductions) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">البنود (بدلات / استقطاعات)</label>
                    
                    {{-- Existing Items --}}
                    @if(count($form['items'] ?? []) > 0)
                        <div class="space-y-2 mb-4">
                            @foreach($form['items'] as $index => $item)
                                <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg border border-gray-100 dark:border-gray-600">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $item['name'] }}</span>
                                            <span class="text-xs px-2 py-0.5 rounded {{ $item['type'] === 'allowance' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $item['type'] === 'allowance' ? 'بدل' : 'استقطاع' }}
                                            </span>
                                            @if($item['is_one_time'])
                                                <span class="text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-800">لمرة واحدة</span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="text-gray-600 dark:text-gray-300 font-mono">{{ number_format($item['amount'], 2) }}</span>
                                    <button type="button" wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 p-1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Add New Item --}}
                    <div class="mb-2">
                        <select wire:model.live="newItemComponentId" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">-- اختر بند من القائمة (اختياري) --</option>
                            @foreach($salaryComponents as $component)
                                <option value="{{ $component->id }}">
                                    {{ $component->name }} 
                                    ({{ $component->type === 'allowance' ? 'بدل' : 'خصم' }})
                                    - 
                                    {{ $component->is_percentage ? $component->percentage_value . '%' : $component->fixed_value }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="sm:col-span-4">
                            <input type="text" wire:model="newItemName" placeholder="اسم البند" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div class="sm:col-span-3">
                            <input type="number" step="0.01" wire:model="newItemAmount" placeholder="المبلغ" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div class="sm:col-span-2">
                            <select wire:model="newItemType" class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="allowance">بدل</option>
                                <option value="deduction">استقطاع</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2 flex items-center">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="newItemIsOneTime" class="sr-only peer">
                                <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                <span class="ms-2 text-xs font-medium text-gray-900 dark:text-gray-300">لمرة واحدة</span>
                            </label>
                        </div>
                        <div class="sm:col-span-1">
                            <button type="button" wire:click="addItem" class="w-full h-full flex items-center justify-center px-2 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ملاحظات</label>
                    <textarea wire:model="form.notes" rows="3" class="w-full px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="ملاحظات اختيارية..."></textarea>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" wire:click="closeForm" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        إلغاء
                    </button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                        {{ $editingId ? 'تحديث' : 'حفظ' }}
                    </button>
                </div>
            </form>
        </div>
    </x-ui.modal>
</div>

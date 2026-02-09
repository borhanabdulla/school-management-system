<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-slate-800">إدارة بنود الراتب</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Section -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">
                    {{ $editingId ? 'تعديل بند' : 'إضافة بند جديد' }}
                </h3>

                <form wire:submit.prevent="save" class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">اسم البند</label>
                        <input type="text" wire:model="name" class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Type -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">النوع</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model.live="type" value="allowance" class="text-purple-600 focus:ring-purple-500">
                                <span class="text-sm text-slate-700">استحقاق (بدل)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model.live="type" value="deduction" class="text-red-600 focus:ring-red-500">
                                <span class="text-sm text-slate-700">استقطاع (خصم)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Calculation Method -->
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer mb-2">
                            <input type="checkbox" wire:model.live="is_percentage" class="rounded text-purple-600 focus:ring-purple-500">
                            <span class="text-sm font-medium text-slate-700">حساب كنسبة مئوية؟</span>
                        </label>

                        @if($is_percentage)
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">النسبة (%)</label>
                                <div class="relative">
                                    <input type="number" step="0.01" wire:model="percentage_value" class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500 pl-8">
                                    <span class="absolute left-3 top-2.5 text-slate-400">%</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">نسبة من الراتب الأساسي</p>
                                @error('percentage_value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @else
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">المبلغ الثابت</label>
                                <div class="relative">
                                    <input type="number" step="0.01" wire:model="fixed_value" class="w-full rounded-lg border-slate-300 focus:border-purple-500 focus:ring-purple-500 pl-16">
                                    <span class="absolute left-3 top-2.5 text-slate-400">SAR</span>
                                </div>
                                @error('fixed_value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    <!-- Active Status -->
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="rounded text-purple-600 focus:ring-purple-500">
                            <span class="text-sm text-slate-700">نشط</span>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="flex-1 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                            {{ $editingId ? 'تحديث' : 'حفظ' }}
                        </button>
                        @if($editingId)
                            <button type="button" wire:click="resetForm" class="bg-slate-100 text-slate-700 px-4 py-2 rounded-lg hover:bg-slate-200 transition-colors">
                                إلغاء
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- List Section -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <table class="w-full text-right">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-medium text-slate-500 uppercase">الاسم</th>
                            <th class="px-6 py-3 text-xs font-medium text-slate-500 uppercase">النوع</th>
                            <th class="px-6 py-3 text-xs font-medium text-slate-500 uppercase">القيمة</th>
                            <th class="px-6 py-3 text-xs font-medium text-slate-500 uppercase">الحالة</th>
                            <th class="px-6 py-3 text-xs font-medium text-slate-500 uppercase">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($components as $component)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $component->name }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $component->type === 'allowance' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $component->type === 'allowance' ? 'استحقاق' : 'استقطاع' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    @if($component->is_percentage)
                                        {{ $component->percentage_value }}% <span class="text-xs text-slate-400">(من الأساسي)</span>
                                    @else
                                        {{ number_format($component->fixed_value, 2) }} SAR
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $component->is_active ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-800' }}">
                                        {{ $component->is_active ? 'نشط' : 'غير نشط' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <div class="flex gap-3">
                                        <button wire:click="edit({{ $component->id }})" class="text-blue-600 hover:text-blue-900">تعديل</button>
                                        <button wire:click="delete({{ $component->id }})" wire:confirm="هل أنت متأكد من حذف هذا البند؟" class="text-red-600 hover:text-red-900">حذف</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    لا توجد بنود معرفة حتى الآن.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-slate-200">
                    {{ $components->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

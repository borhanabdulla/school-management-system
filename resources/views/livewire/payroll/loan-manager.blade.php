<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">إدارة السلف</h1>
                <p class="text-gray-500 dark:text-gray-400">متابعة سلف الموظفين والأقساط</p>
            </div>
            <button wire:click="$set('showCreateModal', true)" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">
                تسجيل سلفة جديدة
            </button>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-6 flex gap-4">
            <input type="text" wire:model.live="search" placeholder="بحث باسم الموظف..." class="flex-1 px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            <select wire:model.live="statusFilter" class="px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">الكل</option>
                <option value="approved">نشط (Approved)</option>
                <option value="completed">مكتمل (Completed)</option>
            </select>
        </div>

        {{-- Loans Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($loans as $loan)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white">{{ $loan->staff->full_name }}</h3>
                            <p class="text-sm text-gray-500">{{ $loan->created_at->format('Y/m/d') }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full {{ $loan->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $loan->status }}
                        </span>
                    </div>
                    
                    <div class="mb-4">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600 dark:text-gray-400">المدفوع</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ number_format($loan->paid_amount) }} / {{ number_format($loan->amount) }}</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                            <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $loan->progress }}%"></div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center text-sm pt-4 border-t border-gray-100 dark:border-gray-700">
                        <div>
                            <p class="text-gray-500">القسط الشهري</p>
                            <p class="font-bold text-gray-900 dark:text-white">{{ number_format($loan->monthly_installment) }} ر.س</p>
                        </div>
                        <div class="text-left">
                            <p class="text-gray-500">المتبقي</p>
                            <p class="font-bold text-gray-900 dark:text-white">{{ $loan->installments_count - $loan->installments()->where('status', 'paid')->count() }} شهر</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="mt-4">
            {{ $loans->links() }}
        </div>

        {{-- Create Modal --}}
        <x-ui.modal wire:model="showCreateModal">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4 dark:text-white">تسجيل سلفة جديدة</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1 dark:text-gray-300">الموظف</label>
                        <select wire:model="staff_id" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">اختر موظف...</option>
                            @foreach($staffList as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 dark:text-gray-300">المبلغ (ر.س)</label>
                            <input type="number" wire:model.live="amount" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 dark:text-gray-300">عدد الأقساط (شهر)</label>
                            <input type="number" wire:model.live="installments_count" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>

                    <div class="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg text-center">
                        <p class="text-sm text-indigo-800 dark:text-indigo-300">القسط الشهري المتوقع</p>
                        <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $monthly_installment ?? 0 }} ر.س</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1 dark:text-gray-300">تاريخ بدء الخصم</label>
                        <input type="date" wire:model="start_date" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1 dark:text-gray-300">ملاحظات</label>
                        <textarea wire:model="reason" class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button wire:click="$set('showCreateModal', false)" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">إلغاء</button>
                    <button wire:click="create" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">حفظ واعتماد</button>
                </div>
            </div>
        </x-ui.modal>
    </div>
</div>

<div class="max-w-2xl mx-auto py-8">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">تقديم طلب إجازة</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">يرجى تعبئة النموذج أدناه لتقديم طلب إجازة جديد</p>
        </div>

        <div class="p-6">
            <form wire:submit.prevent="submit" class="space-y-6">
                <!-- Leave Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">نوع الإجازة</label>
                    <select wire:model="leave_type_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">اختر نوع الإجازة...</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }} (الرصيد: {{ $type->days_per_year }} يوم)</option>
                        @endforeach
                    </select>
                    @error('leave_type_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Dates -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">تاريخ البداية</label>
                        <input type="date" wire:model.live="start_date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">تاريخ النهاية</label>
                        <input type="date" wire:model.live="end_date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Days Count Display -->
                @if($days_count > 0)
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg flex items-center">
                        <svg class="w-5 h-5 text-blue-500 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-blue-700 dark:text-blue-300 font-medium">مدة الإجازة: {{ $days_count }} يوم</span>
                    </div>
                @endif

                <!-- Reason -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">سبب الإجازة</label>
                    <textarea wire:model="reason" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="يرجى توضيح سبب طلب الإجازة..."></textarea>
                    @error('reason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Attachment -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">مرفقات (اختياري)</label>
                    <input type="file" wire:model="attachment" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-1 text-xs text-gray-500">الحد الأقصى 2 ميجابايت (PDF, Images)</p>
                    @error('attachment') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                        <span wire:loading.remove>تقديم الطلب</span>
                        <span wire:loading>جاري التقديم...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

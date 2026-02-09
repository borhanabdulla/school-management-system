<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium leading-6 text-gray-900">تسجيل دفعة جديدة</h3>
        <button wire:click="$dispatch('closeModal')" class="text-gray-400 hover:text-gray-500">
            <span class="sr-only">Close</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    @if($isLocked)
        <div class="rounded-md bg-yellow-50 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">تنبيه</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>السنة المالية لهذه الفاتورة مغلقة، لكن يُسمح بتسجيل الدفعات.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="space-y-4">
        <!-- Info Card -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">المبلغ المتبقي:</span>
                <span class="font-bold text-gray-900">{{ number_format($remainingAmount, 2) }}</span>
            </div>
        </div>

        <!-- Error Message -->
        @if($errors->has('general'))
            <div class="rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">{{ $errors->first('general') }}</h3>
                    </div>
                </div>
            </div>
        @endif

        <!-- Amount -->
        <div>
            <label for="amount" class="block text-sm font-medium text-gray-700">المبلغ المدفوع</label>
            <div class="mt-1 relative rounded-md shadow-sm">
                <input type="number" wire:model="amount" id="amount" step="0.01" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-10 sm:text-sm border-gray-300 rounded-md" placeholder="0.00">
            </div>
            @error('amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Method -->
        <div>
            <label for="method" class="block text-sm font-medium text-gray-700">طريقة الدفع</label>
            <select wire:model="method" id="method" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                @foreach(\App\Domains\Finance\Enums\PaymentMethod::cases() as $method)
                    <option value="{{ $method->value }}">{{ $method->label() }}</option>
                @endforeach
            </select>
            @error('method') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Notes -->
        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">ملاحظات (اختياري)</label>
            <textarea wire:model="notes" id="notes" rows="2" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
            @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="mt-5 sm:mt-6">
        <button type="button" wire:click="record" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:text-sm">
            تسجيل الدفعة
        </button>
    </div>
</div>

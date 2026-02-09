<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium leading-6 text-gray-900">تطبيق خصم يدوي</h3>
        <button wire:click="$dispatch('closeModal')" class="text-gray-400 hover:text-gray-500">
            <span class="sr-only">Close</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    @if($isLocked)
        <div class="rounded-md bg-red-50 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">السنة المالية مغلقة</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>لا يمكن تعديل الفواتير أو تطبيق خصومات لهذه السنة.</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="space-y-4">
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

            <!-- Item Selection -->
            <div>
                <label for="item" class="block text-sm font-medium text-gray-700">البند</label>
                <select wire:model="invoice_item_id" id="item" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">اختر البند...</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->feeType->name }} ({{ number_format($item->amount, 2) }})</option>
                    @endforeach
                </select>
                @error('invoice_item_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Discount Selection -->
            <div>
                <label for="discount" class="block text-sm font-medium text-gray-700">نوع الخصم</label>
                <select wire:model="discount_id" id="discount" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">اختر الخصم...</option>
                    @foreach($discounts as $discount)
                        <option value="{{ $discount->id }}">{{ $discount->name }} ({{ $discount->value }}{{ $discount->type === 'percentage' ? '%' : '' }})</option>
                    @endforeach
                </select>
                @error('discount_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Reason -->
            <div>
                <label for="reason" class="block text-sm font-medium text-gray-700">سبب الخصم (إلزامي)</label>
                <textarea wire:model="reason" id="reason" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="وضح سبب منح الخصم..."></textarea>
                @error('reason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="mt-5 sm:mt-6">
            <button type="button" wire:click="apply" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">
                تطبيق الخصم
            </button>
        </div>
    @endif
</div>

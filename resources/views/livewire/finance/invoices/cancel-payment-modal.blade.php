<div class="p-6">
    <div class="mb-4">
        <h3 class="text-lg font-medium text-gray-900">تأكيد إلغاء الدفعة</h3>
        <p class="text-sm text-gray-500 mt-1">
            أنت على وشك إلغاء دفعة بقيمة <strong>{{ number_format($payment->amount, 2) }}</strong>. هذه العملية لا يمكن التراجع عنها.
        </p>
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
                    <h3 class="text-sm font-medium text-yellow-800">سنة مالية مغلقة</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>هذه الدفعة تابعة لسنة مغلقة. الإلغاء سيؤثر على الرصيد المرحل (Arrears). يرجى ذكر السبب بدقة.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($errors->has('base'))
        <div class="rounded-md bg-red-50 p-4 mb-4">
            <div class="flex">
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">خطأ</h3>
                    <div class="mt-2 text-sm text-red-700">
                        {{ $errors->first('base') }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="mb-4">
        <label for="reason" class="block text-sm font-medium text-gray-700">سبب الإلغاء <span class="text-red-500">*</span></label>
        <textarea wire:model="reason" id="reason" rows="3" class="shadow-sm focus:ring-red-500 focus:border-red-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md" placeholder="اكتب سبب إلغاء الدفعة..."></textarea>
        @error('reason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>

    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
        <button wire:click="cancel" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:col-start-2 sm:text-sm">
            تأكيد الإلغاء
        </button>
        <button wire:click="$dispatch('closeModal')" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
            تراجع
        </button>
    </div>
</div>

<div class="max-w-2xl mx-auto space-y-8">
    {{-- Pass Score & Grace --}}
    <div class="bg-gray-50 dark:bg-gray-700/30 p-6 rounded-xl space-y-4">
        <h3 class="font-bold text-gray-800 dark:text-white border-b pb-2">قواعد النجاح والرأفة</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">درجة النجاح الافتراضية</label>
                <input type="number" wire:model="defaultPassScore" class="w-full rounded-lg border-gray-300 dark:bg-gray-700">
                @error('defaultPassScore')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">حد درجات الرأفة</label>
                <input type="number" wire:model="graceMarksLimit" class="w-full rounded-lg border-gray-300 dark:bg-gray-700">
                @error('graceMarksLimit')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    {{-- Term Weights --}}
    <div class="bg-gray-50 dark:bg-gray-700/30 p-6 rounded-xl space-y-4">
        <h3 class="font-bold text-gray-800 dark:text-white border-b pb-2">أوزان الفصول الدراسية</h3>
        @php
            $terms = app(\App\Domains\Academic\Term\Services\TermLookupService::class)->getActiveTerms();
        @endphp
        @foreach($terms as $term)
            <div class="flex items-center justify-between">
                <span class="text-gray-700 dark:text-gray-300">{{ $term->name }}</span>
                <div class="flex items-center gap-2">
                    <input type="number" wire:model="termWeights.{{ $term->id }}" class="w-24 rounded-lg border-gray-300 dark:bg-gray-700 text-center">
                    <span class="text-gray-500">%</span>
                </div>
            </div>
        @endforeach
        <div class="text-sm text-gray-500 mt-2">
            المجموع الحالي: <span class="{{ array_sum($termWeights) == 100 ? 'text-green-600' : 'text-red-600' }} font-bold">{{ array_sum($termWeights) }}%</span>
        </div>
@error('termWeights')
            <span class="text-xs text-red-600">{{ $message }}</span>
        @enderror
    </div>

    <div class="flex justify-end">
        <button wire:click="save"
                wire:loading.attr="disabled"
                class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-bold shadow-lg transform hover:-translate-y-1 transition disabled:opacity-50">
            <span wire:loading.remove>حفظ الإعدادات العامة</span>
            <span wire:loading>جاري الحفظ...</span>
        </button>
    </div>
</div>

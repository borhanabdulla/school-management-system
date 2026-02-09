<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white">سلم التقديرات (Grade Scale)</h3>
        <button wire:click="save"
                wire:loading.attr="disabled"
                class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 disabled:opacity-50">
            <span wire:loading.remove>حفظ التغييرات</span>
            <span wire:loading>جاري الحفظ...</span>
        </button>
    </div>

    @error('gradeScale')
        <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
            {{ $message }}
        </div>
    @enderror

    <div class="bg-white dark:bg-gray-900 rounded-xl shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">التقدير</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">من %</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">إلى %</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">اللون</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($gradeScale as $index => $scale)
                    <tr>
                        <td class="px-6 py-4">
                            <input type="text" wire:model="gradeScale.{{ $index }}.grade" class="w-20 rounded border-gray-300 dark:bg-gray-700">
                        </td>
                        <td class="px-6 py-4">
                            <input type="number" wire:model="gradeScale.{{ $index }}.min" class="w-20 rounded border-gray-300 dark:bg-gray-700">
                        </td>
                        <td class="px-6 py-4">
                            <input type="number" wire:model="gradeScale.{{ $index }}.max" class="w-20 rounded border-gray-300 dark:bg-gray-700">
                        </td>
                        <td class="px-6 py-4 flex items-center gap-2">
                            <input type="color" wire:model="gradeScale.{{ $index }}.color" class="h-8 w-8 rounded cursor-pointer">
                            <span class="text-xs text-gray-500">{{ $scale['color'] ?? '' }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

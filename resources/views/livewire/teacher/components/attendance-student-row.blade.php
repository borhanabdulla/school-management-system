@props(['student', 'index'])

<tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ $student['is_saved_previously'] ? 'bg-green-50/30 dark:bg-green-900/10' : '' }}">
    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
        <div class="flex items-center gap-2">
            {{ $index + 1 }}
            @if($student['is_saved_previously'])
                <span class="w-2 h-2 bg-green-500 rounded-full" title="تم الرصد مسبقاً"></span>
            @endif
        </div>
    </td>
    <td class="px-4 py-4 whitespace-nowrap">
        <div class="flex items-center">
            <div class="h-8 w-8 rounded-full ml-3 bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-xs">
                {{ mb_substr($student['name'], 0, 1) }}
            </div>
            <div class="text-sm font-medium text-gray-900 dark:text-white">
                {{ $student['name'] }}
            </div>
        </div>
    </td>
    <td class="px-4 py-4 text-center">
        <div class="inline-flex flex-wrap justify-center gap-1" role="group">
            {{-- زر حاضر --}}
            <button type="button" wire:click="setStatus({{ $index }}, 'present')"
                class="px-3 py-1.5 text-xs font-medium border rounded-lg transition-colors
                {{ $student['status'] === 'present' 
                    ? 'bg-green-600 text-white border-green-600 hover:bg-green-700' 
                    : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600' }}">
                حاضر
            </button>

            {{-- زر غائب --}}
            <button type="button" wire:click="setStatus({{ $index }}, 'absent')"
                class="px-3 py-1.5 text-xs font-medium border rounded-lg transition-colors
                {{ $student['status'] === 'absent' 
                    ? 'bg-red-600 text-white border-red-600 hover:bg-red-700' 
                    : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600' }}">
                غائب
            </button>

            {{-- زر متأخر --}}
            <button type="button" wire:click="setStatus({{ $index }}, 'late')"
                class="px-3 py-1.5 text-xs font-medium border rounded-lg transition-colors
                {{ $student['status'] === 'late' 
                    ? 'bg-amber-500 text-white border-amber-500 hover:bg-amber-600' 
                    : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600' }}">
                تأخير
            </button>

            {{-- زر معذور --}}
            <button type="button" wire:click="setStatus({{ $index }}, 'excused')"
                class="px-3 py-1.5 text-xs font-medium border rounded-lg transition-colors
                {{ $student['status'] === 'excused' 
                    ? 'bg-blue-500 text-white border-blue-500 hover:bg-blue-600' 
                    : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600' }}">
                معذور
            </button>
        </div>
    </td>
    <td class="px-4 py-4">
        <div class="flex flex-col gap-2">
            {{-- حقل دقائق التأخير (يظهر فقط إذا كان متأخراً) --}}
            @if($student['status'] === 'late')
                <div class="flex items-center animate-fadeIn">
                    <input type="number" wire:model="students.{{ $index }}.delay_minutes" placeholder="دقيقة" min="1"
                        class="w-20 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-amber-500 focus:border-amber-500">
                    <span class="text-xs text-gray-500 dark:text-gray-400 mr-2">دقيقة</span>
                </div>
            @endif
            
            {{-- ملاحظات --}}
            <input type="text" wire:model="students.{{ $index }}.remarks" placeholder="ملاحظة..."
                class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400">
        </div>
    </td>
</tr>

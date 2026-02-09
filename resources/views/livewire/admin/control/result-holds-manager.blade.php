<div class="py-6" dir="rtl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                إدارة حجب النتائج - {{ $this->session->name }}
            </h1>
            <a href="{{ route('control.dashboard') }}" class="text-indigo-600 hover:text-indigo-900">
                عودة للوحة التحكم
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <input type="text" wire:model.live="search" placeholder="بحث عن طالب..."
                       class="w-full md:w-1/3 rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">رقم الجلوس</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">الطالب</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">الصف / الفصل</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">حالة الحجب</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">سبب الحجب</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($seatings as $seating)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $seating->seat_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $seating->student->full_name_ar }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $seating->student->currentClassSection->grade->name }} - {{ $seating->student->currentClassSection->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button wire:click="toggleWithhold({{ $seating->id }})"
                                        class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 {{ $seating->is_withheld ? 'bg-red-600' : 'bg-gray-200' }}">
                                    <span class="translate-x-0 inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200 {{ $seating->is_withheld ? '-translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                                <span class="mr-2 text-sm {{ $seating->is_withheld ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                                    {{ $seating->is_withheld ? 'محجوب' : 'متاح' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @if($editingSeatingId === $seating->id)
                                    <div class="flex items-center gap-2">
                                        <input type="text" wire:model="withholdReason" class="text-sm rounded border-gray-300 dark:bg-gray-700 dark:text-white">
                                        <button wire:click="saveReason" class="text-green-600 hover:text-green-900">حفظ</button>
                                        <button wire:click="cancelEdit" class="text-gray-600 hover:text-gray-900">إلغاء</button>
                                    </div>
                                @else
                                    {{ $seating->withhold_reason ?? '-' }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @if($seating->is_withheld && $editingSeatingId !== $seating->id)
                                    <button wire:click="editReason({{ $seating->id }})" class="text-indigo-600 hover:text-indigo-900">
                                        تعديل السبب
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4">
                {{ $seatings->links() }}
            </div>
        </div>
    </div>
</div>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">إدارة أولياء الأمور</h2>
        <a href="{{ route('guardians.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            إضافة ولي أمر
        </a>
    </div>

    <div class="mb-4">
        <input wire:model.live="search" type="search" placeholder="بحث بالاسم، الهوية، أو الهاتف..." class="w-full max-w-md px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">الاسم</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">الهوية / الهاتف</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">الطلاب المرتبطين</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($guardians as $guardian)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-500 font-bold">
                                    {{ substr($guardian->first_name, 0, 1) }}
                                </div>
                                <div class="mr-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $guardian->first_name }} {{ $guardian->last_name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $guardian->user->email ?? 'لا يوجد بريد إلكتروني' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">{{ $guardian->national_id }}</div>
                            <div class="text-sm text-gray-500">{{ $guardian->phone }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex -space-x-2 overflow-hidden">
                                @foreach($guardian->students as $student)
                                    <span class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-800" title="{{ $student->first_name_ar }}">
                                        {{ substr($student->first_name_ar, 0, 1) }}
                                    </span>
                                @endforeach
                                @if($guardian->students->count() > 3)
                                    <span class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-gray-100 flex items-center justify-center text-xs text-gray-500">
                                        +{{ $guardian->students->count() - 3 }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('guardians.show', $guardian->id) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900 ml-4">عرض</a>
                            <button wire:click="delete({{ $guardian->id }})" wire:confirm="هل أنت متأكد من حذف ولي الأمر هذا؟" class="text-red-600 hover:text-red-900">حذف</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">لا يوجد أولياء أمور مسجلين</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $guardians->links() }}
    </div>
</div>

<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">طلبات الإجازة</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">مراجعة واعتماد طلبات إجازات الموظفين</p>
    </div>

    <!-- Filters -->
    <div class="mb-4">
        </div>

    <div class="mb-6 flex space-x-4 space-x-reverse">
        <button wire:click="$set('statusFilter', 'pending')" class="px-4 py-2 rounded-lg text-sm font-medium {{ $statusFilter === 'pending' ? 'bg-indigo-100 text-indigo-700' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
            قيد الانتظار
        </button>
        <button wire:click="$set('statusFilter', 'approved')" class="px-4 py-2 rounded-lg text-sm font-medium {{ $statusFilter === 'approved' ? 'bg-green-100 text-green-700' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
            المقبولة
        </button>
        <button wire:click="$set('statusFilter', 'rejected')" class="px-4 py-2 rounded-lg text-sm font-medium {{ $statusFilter === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
            المرفوضة
        </button>
        <button wire:click="$set('statusFilter', '')" class="px-4 py-2 rounded-lg text-sm font-medium {{ $statusFilter === '' ? 'bg-gray-200 text-gray-800' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
            الكل
        </button>
    </div>

    <!-- List -->
    <div class="space-y-4">
        @forelse($requests as $request)
            <div wire:key="request-{{ $request->id }}" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border-r-4 {{ $request->status === 'pending' ? 'border-amber-400' : ($request->status === 'approved' ? 'border-green-500' : 'border-red-500') }}">
                <div class="flex justify-between items-start">
                    <div class="flex items-center">
                        <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center text-xl font-bold text-gray-600 ml-4">
                            {{ substr($request->staff->first_name, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $request->staff->full_name }}</h3>
                            <p class="text-sm text-gray-500">{{ $request->leaveType->name }} • {{ $request->days_count }} يوم</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-{{ $request->status_color }}-100 text-{{ $request->status_color }}-800">
                        {{ $request->status_label }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600 dark:text-gray-300">
                    <div>
                        <span class="block text-xs text-gray-400">من تاريخ</span>
                        {{ $request->start_date->format('Y-m-d') }}
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400">إلى تاريخ</span>
                        {{ $request->end_date->format('Y-m-d') }}
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400">تاريخ الطلب</span>
                        {{ $request->created_at->diffForHumans() }}
                    </div>
                </div>

                <div class="mt-4 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg text-sm text-gray-700 dark:text-gray-300">
                    <span class="font-bold block mb-1">السبب:</span>
                    {{ $request->reason }}
                </div>

                @if($request->attachment)
                    <div class="mt-2">
                        <a href="{{ Storage::url($request->attachment) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-sm flex items-center">
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                            عرض المرفق
                        </a>
                    </div>
                @endif

                @if($request->status === 'pending')
                    <div class="mt-6 flex justify-end space-x-3 space-x-reverse border-t pt-4 border-gray-100 dark:border-gray-700">
                        <button wire:click="approve({{ $request->id }})" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium transition">
                            قبول الطلب
                        </button>
                        <button wire:click="confirmReject({{ $request->id }})" class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 text-sm font-medium transition">
                            رفض
                        </button>
                    </div>
                @endif
                
                @if($request->status === 'rejected' && $request->rejection_reason)
                    <div class="mt-4 bg-red-50 p-3 rounded text-red-800 text-sm">
                        <strong>سبب الرفض:</strong> {{ $request->rejection_reason }}
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl">
                <p class="text-gray-500">لا توجد طلبات إجازة في هذه القائمة.</p>
            </div>
        @endforelse

        {{ $requests->links() }}
    </div>

    <!-- Reject Modal -->
    @if($rejectingId)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="cancelReject"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">رفض طلب الإجازة</h3>
                        <textarea wire:model="rejectionReason" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="سبب الرفض..."></textarea>
                        @error('rejectionReason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="reject" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            تأكيد الرفض
                        </button>
                        <button type="button" wire:click="cancelReject" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            إلغاء
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

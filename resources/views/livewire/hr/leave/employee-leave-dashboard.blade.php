<div>
    <div class="mb-8 flex justify-between items-end">
        <div>
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white font-display">لوحة إجازاتي</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-1">نظرة عامة على رصيدك وسجل طلباتك</p>
        </div>
        <a href="{{ route('hr.leave.request') }}" class="btn btn-primary shadow-lg shadow-indigo-500/30 transform hover:-translate-y-1 transition-all duration-300">
            <span class="mr-2">+</span> تقديم طلب جديد
        </a>
    </div>

    <!-- Balance Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @foreach($balances as $balance)
            <x-stat-card 
                label="{{ $balance['type'] }}" 
                value="{{ $balance['remaining'] }} يوم"
                icon='<svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>'
                gradient="{{ $loop->index % 2 == 0 ? 'purple-blue' : 'orange-pink' }}"
            >
                <div class="mt-4">
                    <div class="flex justify-between text-xs text-white/80 mb-1">
                        <span>المستخدم: {{ $balance['used'] }}</span>
                        <span>الإجمالي: {{ $balance['total'] }}</span>
                    </div>
                    <div class="w-full bg-black/20 rounded-full h-1.5 backdrop-blur-sm">
                        <div class="bg-white/90 h-1.5 rounded-full transition-all duration-1000 ease-out" style="width: {{ $balance['percentage'] }}%"></div>
                    </div>
                </div>
            </x-stat-card>
        @endforeach
    </div>

    <!-- Recent Requests -->
    <x-card title="سجل الطلبات الأخيرة">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider rounded-r-lg">نوع الإجازة</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">التاريخ</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">المدة</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الحالة</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider rounded-l-lg">ملاحظات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($requests as $request)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $request->leaveType->name }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $request->start_date->format('Y/m/d') }} 
                                <span class="text-xs text-gray-400 mx-1">إلى</span>
                                {{ $request->end_date->format('Y/m/d') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $request->days_count }} يوم
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge :variant="$request->status === 'approved' ? 'success' : ($request->status === 'rejected' ? 'danger' : 'warning')">
                                    {{ $request->status_label }}
                                </x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @if($request->status === 'rejected')
                                    <span class="text-red-500 text-xs" title="{{ $request->rejection_reason }}">
                                        {{ Str::limit($request->rejection_reason, 20) }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                    <p>لا توجد طلبات إجازة سابقة.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $requests->links() }}
        </div>
    </x-card>
</div>

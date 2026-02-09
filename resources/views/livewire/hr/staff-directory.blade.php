<div class="min-h-screen bg-gray-50/50 dark:bg-gray-900/50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">دليل الموظفين</h1>
                <p class="mt-2 text-gray-500 dark:text-gray-400 text-lg">إدارة وتصفح ملفات جميع موظفي المدرسة</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('hr.staff.create') }}" 
                   class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-medium shadow-lg shadow-indigo-500/30 transition-all hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    إضافة موظف
                </a>
            </div>
        </div>

        {{-- Filters Bar --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 mb-8 sticky top-4 z-30 backdrop-blur-xl bg-white/90 dark:bg-gray-800/90 supports-[backdrop-filter]:bg-white/60">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                {{-- Search --}}
                <div class="md:col-span-5 relative group">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        class="w-full pr-11 pl-4 py-3 rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400"
                        placeholder="ابحث بالاسم، البريد، أو الرقم الوظيفي...">
                </div>

                {{-- Status Filter --}}
                <div class="md:col-span-3 relative">
                    <select wire:model.live="status"
                        class="w-full px-4 py-3 rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                        <option value="">جميع الحالات</option>
                        <option value="active">نشط</option>
                        <option value="on_leave">في إجازة</option>
                        <option value="terminated">منتهي</option>
                    </select>
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                {{-- Type Filter --}}
                <div class="md:col-span-4 relative">
                    <select wire:model.live="employment_type"
                        class="w-full px-4 py-3 rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                        <option value="">جميع أنواع التوظيف</option>
                        <option value="full_time">دوام كامل</option>
                        <option value="part_time">دوام جزئي</option>
                        <option value="contractor">مقاول</option>
                    </select>
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Staff Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse ($staffList as $member)
                <div class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-indigo-100 dark:hover:border-indigo-900/50 transition-all duration-300 flex flex-col overflow-hidden relative">
                    
                    {{-- Status Badge --}}
                    <div class="absolute top-4 left-4 z-10">
                        @php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 ring-green-500/20',
                                'on_leave' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300 ring-orange-500/20',
                                'terminated' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 ring-red-500/20',
                            ];
                            $statusLabels = [
                                'active' => 'نشط',
                                'on_leave' => 'في إجازة',
                                'terminated' => 'منتهي',
                            ];
                            $statusValue = $member->status instanceof \BackedEnum ? $member->status->value : $member->status;
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold ring-1 ring-inset {{ $statusColors[$statusValue] ?? 'bg-gray-100 text-gray-700 ring-gray-500/20' }} backdrop-blur-md bg-opacity-90">
                            {{ $statusLabels[$statusValue] ?? $member->status }}
                        </span>
                    </div>

                    {{-- Card Header & Avatar --}}
                    <div class="p-6 pb-0 flex flex-col items-center text-center">
                        <div class="relative mb-4 group-hover:scale-105 transition-transform duration-300">
                            <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-3xl font-bold shadow-lg shadow-indigo-500/20">
                                {{ mb_substr($member->first_name, 0, 1) }}
                            </div>
                            @if($member->teacher)
                                <div class="absolute -bottom-2 -right-2 bg-white dark:bg-gray-800 p-1.5 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700" title="معلم">
                                    <div class="w-6 h-6 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center text-purple-600 dark:text-purple-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1 line-clamp-1">
                            <a href="{{ route('hr.staff.show', $member) }}" class="hover:text-indigo-600 transition-colors">
                                {{ $member->full_name }}
                            </a>
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-4">{{ $member->job_title ?? 'غير محدد' }}</p>
                        
                        {{-- Quick Info --}}
                        <div class="w-full grid grid-cols-2 gap-2 mb-6">
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-2 text-center">
                                <span class="block text-xs text-gray-400 mb-1">الرقم الوظيفي</span>
                                <span class="block text-sm font-bold text-gray-700 dark:text-gray-200 font-mono">{{ $member->employee_number }}</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-2 text-center">
                                <span class="block text-xs text-gray-400 mb-1">الالتحاق</span>
                                <span class="block text-sm font-bold text-gray-700 dark:text-gray-200">{{ $member->joining_date ? \Carbon\Carbon::parse($member->joining_date)->format('Y/m') : '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Actions Footer --}}
                    <div class="mt-auto border-t border-gray-100 dark:border-gray-700/50 p-4 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between gap-2">
                        <a href="{{ route('hr.staff.show', $member) }}" 
                           class="flex-1 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 hover:text-indigo-600 dark:hover:text-indigo-400 shadow-sm hover:shadow transition-all text-center border border-transparent hover:border-gray-200 dark:hover:border-gray-600">
                            عرض الملف
                        </a>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('hr.staff.edit', $member) }}" class="p-2 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-white dark:hover:bg-gray-700 transition-all" title="تعديل">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button wire:click="confirmDelete({{ $member->id }})" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-white dark:hover:bg-gray-700 transition-all" title="حذف">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">لا يوجد موظفون مطابقون</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto mb-8">لم يتم العثور على أي موظفين يطابقون معايير البحث الحالية. جرب تغيير الفلاتر أو إضافة موظف جديد.</p>
                    <a href="{{ route('hr.staff.create') }}" class="inline-flex items-center px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-medium shadow-lg shadow-indigo-500/30 transition-all">
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        إضافة موظف جديد
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($staffList->hasPages())
            <div class="mt-8">
                {{ $staffList->links() }}
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showDeleteModal', false)"></div>

                <div class="inline-block w-full max-w-lg p-8 my-8 overflow-hidden text-right align-middle transition-all transform bg-white dark:bg-gray-800 shadow-2xl rounded-3xl relative">
                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-6 rounded-full bg-red-100 dark:bg-red-900/30">
                        <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>

                    <h3 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white text-center">
                        حذف الموظف
                    </h3>
                    <p class="text-center text-gray-500 dark:text-gray-400 mb-8">
                        أنت على وشك حذف الموظف <span class="font-bold text-gray-900 dark:text-white">{{ $staffToDelete?->full_name }}</span>.
                    </p>

                    @if (!$deleteCheckResult['can_delete'])
                        <div class="p-4 mb-6 rounded-xl bg-orange-50 dark:bg-orange-900/20 border border-orange-100 dark:border-orange-800">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-orange-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <div>
                                    <h4 class="font-bold text-orange-800 dark:text-orange-300 text-sm mb-1">لا يمكن الحذف النهائي</h4>
                                    <ul class="list-disc list-inside text-xs text-orange-700 dark:text-orange-400 space-y-1">
                                        @foreach ($deleteCheckResult['reasons'] as $reason)
                                            <li>{{ $reason }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <button type="button" wire:click="$set('showDeleteModal', false)"
                            class="px-6 py-3 text-sm font-bold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            إلغاء
                        </button>
                        
                        @if ($deleteCheckResult['can_delete'])
                            <button type="button" wire:click="delete"
                                class="px-6 py-3 text-sm font-bold text-white bg-red-600 rounded-xl hover:bg-red-700 shadow-lg shadow-red-500/30 transition-all">
                                تأكيد الحذف
                            </button>
                        @else
                            <button type="button" wire:click="deactivate"
                                class="px-6 py-3 text-sm font-bold text-orange-700 bg-orange-100 rounded-xl hover:bg-orange-200 dark:text-orange-300 dark:bg-orange-900/30 dark:hover:bg-orange-900/50 transition-all">
                                تعطيل الحساب فقط
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

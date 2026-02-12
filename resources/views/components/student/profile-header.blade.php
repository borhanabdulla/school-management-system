@props(['student', 'canDelete' => false, 'deleteBlockers' => []])

<div class="relative z-0">
    <!-- Cover Background -->
    <div class="relative h-48 w-full overflow-hidden bg-gradient-to-r from-purple-800 to-indigo-900">
        <!-- Abstract Pattern -->
        <div class="absolute inset-0 opacity-20">
            <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <path d="M0 100 C 20 0 50 0 100 100 Z" fill="white" fill-opacity="0.1"></path>
            </svg>
        </div>
        
        <div class="absolute inset-0 bg-black/10"></div>
    </div>

    <!-- Header Content -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative -mt-20 flex flex-col items-center gap-6 pb-6 lg:flex-row lg:items-end lg:gap-8">
            <!-- Profile Photo -->
            <div class="relative shrink-0">
                <div class="h-32 w-32 overflow-hidden rounded-full border-4 border-white bg-white shadow-xl dark:border-slate-800 dark:bg-slate-800 lg:h-40 lg:w-40">
                    <img src="{{ $student->profile_photo_url }}" alt="{{ $student->full_name_ar }}" class="h-full w-full object-cover">
                </div>
                <!-- Active/Inactive Indicator -->
                <div class="absolute bottom-2 right-2 rounded-full border-4 border-white bg-emerald-500 p-2 dark:border-slate-800"></div>
            </div>

            <!-- Student Info -->
            <div class="flex-1 text-center lg:text-right lg:mb-4">
                <div class="flex flex-col lg:flex-row items-center justify-center lg:justify-start gap-4">
                    <h1 class="text-3xl font-bold text-white lg:text-4xl">{{ $student->full_name_ar }}</h1>
                    <div class="bg-white/20 backdrop-blur-md px-3 py-1 rounded-full border border-white/10 shadow-sm">
                        <div class="text-sm font-bold text-white flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ $student->status === \App\Domains\Academic\Student\Enums\StudentStatus::Active ? 'bg-emerald-400' : 'bg-gray-400' }}"></span>
                            {{ $student->status->label() }}
                        </div>
                    </div>
                </div>
                <div class="mt-2 flex flex-wrap items-center justify-center gap-3 text-sm text-purple-100 lg:justify-start">
                    <span class="flex items-center gap-1.5 opacity-90">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                        {{ $student->address ?? 'العنوان غير مسجل' }}
                    </span>
                    <span class="hidden h-1 w-1 rounded-full bg-purple-400 lg:block"></span>
                    <span class="flex items-center gap-1.5 opacity-90">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                        {{ $student->email ?? 'لا يوجد بريد إلكتروني' }}
                    </span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex shrink-0 items-center gap-3 lg:mb-4 text-white lg:text-gray-700 dark:lg:text-gray-200">
                <!-- Delete -->
                @if($canDelete)
                    <button wire:click="delete"
                            wire:confirm="هل أنت متأكد؟"
                            class="flex items-center gap-2 rounded-lg bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/20 lg:bg-rose-50 lg:text-rose-600 lg:hover:bg-rose-100 dark:lg:bg-rose-900/20 dark:lg:text-rose-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        <span>حذف</span>
                    </button>
                @else
                    <div class="relative group">
                         <button disabled
                                class="flex items-center gap-2 rounded-lg bg-white/10 px-4 py-2 text-sm font-semibold text-white/50 cursor-not-allowed lg:bg-gray-100 lg:text-gray-400 dark:lg:bg-slate-800 dark:lg:text-slate-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            <span>حذف</span>
                        </button>
                         <div class="absolute top-full right-0 mt-2 w-64 bg-gray-900 text-white text-xs rounded-lg p-3 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50 shadow-xl border border-gray-700">
                            <div class="font-bold mb-1 text-red-400">لا يمكن الحذف:</div>
                            <ul class="list-disc list-inside space-y-1 text-gray-300">
                                @foreach($deleteBlockers as $blocker)
                                    <li>{{ $blocker }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Edit -->
                <button wire:click="edit"
                        class="flex items-center gap-2 rounded-lg bg-white/20 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/30 lg:bg-purple-600 lg:text-white lg:hover:bg-purple-700 lg:shadow-md lg:shadow-purple-700/20">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                    <span>تعديل</span>
                </button>
            </div>
        </div>
        
        <!-- Mobile Actions (Visible only on small screens) -->
        <div class="lg:hidden flex justify-center pb-6">
             <a href="{{ route('students.index') }}" class="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400">
                &larr; العودة للقائمة
             </a>
        </div>
    </div>
</div>

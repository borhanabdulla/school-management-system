@props(['student'])

<div class="space-y-6">
    <!-- Quick Stats & Status -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">معلومات الطالب</h3>
        </div>
        
        <div class="mt-6 space-y-4">
            <!-- Admission Number -->
            <div class="flex items-center justify-between rounded-xl bg-gray-50 p-3 dark:bg-slate-700/50">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white shadow-sm dark:bg-slate-600">
                        <svg class="h-4 w-4 text-gray-500 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">رقم القيد</span>
                </div>
                <span class="font-mono text-sm font-bold text-gray-900 dark:text-white">{{ $student->admission_number }}</span>
            </div>

            <!-- Current Grade -->
            <div class="flex items-center justify-between rounded-xl bg-gray-50 p-3 dark:bg-slate-700/50">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white shadow-sm dark:bg-slate-600">
                        <svg class="h-4 w-4 text-gray-500 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.499 5.516 50.552 50.552 0 0 0-2.658.813m-15.482 0A50.55 50.55 0 0 1 12 13.489a50.55 50.55 0 0 1 1.518-2.528M20.25 10.5V18" />
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">الصف الحالي</span>
                </div>
                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $student->currentGrade?->name ?? 'غير محدد' }}</span>
            </div>

            <!-- Years Enrolled -->
            <div class="flex items-center justify-between rounded-xl bg-gray-50 p-3 dark:bg-slate-700/50">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white shadow-sm dark:bg-slate-600">
                        <svg class="h-4 w-4 text-gray-500 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0h18M5 10.5h.008v.008H5V10.5Zm0 4.5h.008v.008H5V15Zm0 4.5h.008v.008H5V19.5Z" />
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">تاريخ التسجيل</span>
                </div>
                <div class="text-end">
                    <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $student->created_at->format('Y-m-d') }}</div>
                    <div class="text-[10px] text-gray-400">منذ {{ $student->created_at->diffInYears(now()) }} سنوات</div>
                </div>
            </div>
        </div>

        <div class="mt-6 border-t border-gray-100 pt-6 dark:border-slate-700">
            <h4 class="mb-3 text-xs font-semibold text-gray-500 dark:text-gray-400">حالة الحساب</h4>
            <!-- Account Status Placeholder (To be integrated with Finance) -->
            <div class="flex items-center gap-2 rounded-lg bg-emerald-50 px-3 py-2 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">
                <span class="flex h-2 w-2 rounded-full bg-emerald-500"></span>
                <span class="text-xs font-medium">نشط (لا توجد متأخرات)</span>
            </div>
        </div>
    </div>

    <!-- Contact Info -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <h3 class="mb-4 text-sm font-bold text-gray-900 dark:text-white">معلومات التواصل</h3>
        <div class="space-y-4">
             <!-- Guardian Contact -->
             @if($student->guardians->first())
                <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 p-4 text-white hover:shadow-md transition-shadow">
                    <div class="relative z-10">
                        <div class="text-[10px] text-purple-100 mb-1">ولي الأمر الرئيسي</div>
                        <div class="font-bold text-sm mb-3">{{ $student->guardians->first()->full_name }}</div>
                        <div class="flex items-center gap-2">
                             <a href="tel:{{ $student->guardians->first()->phone }}" class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20 hover:bg-white/30 transition-colors backdrop-blur-sm">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                </svg>
                             </a>
                             <a href="https://wa.me/{{ $student->guardians->first()->phone }}" target="_blank" class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20 hover:bg-white/30 transition-colors backdrop-blur-sm">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
                                </svg>
                             </a>
                        </div>
                    </div>
                    <!-- Pattern -->
                    <div class="absolute -bottom-4 -right-4 h-24 w-24 rounded-full bg-white/10 blur-xl"></div>
                </div>
            @else
                <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 py-6 text-center dark:border-slate-700">
                    <svg class="h-8 w-8 text-gray-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                         <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2 2 0 1 1-4 0 2 2 0 0 1 4 0ZM7 10a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z" />
                    </svg>
                    <div class="mt-2 text-xs font-medium text-gray-500">لا يوجد ولي أمر مرتبط</div>
                </div>
            @endif
        </div>
    </div>
</div>

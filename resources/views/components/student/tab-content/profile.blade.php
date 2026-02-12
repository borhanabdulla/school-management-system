@props(['student'])

<div class="space-y-6">
    <!-- Personal Details Section -->
    <section>
        <div class="flex items-center gap-3 mb-4">
            <div class="p-2 bg-purple-100 text-purple-600 rounded-lg dark:bg-purple-900/30 dark:text-purple-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">البيانات الشخصية</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
             <!-- Arabic Name -->
            <div class="flex items-start gap-4 p-4 rounded-xl bg-gray-50 border border-gray-100 dark:bg-slate-800/50 dark:border-slate-700">
                <div class="mt-1">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">الاسم بالكامل (عربي)</span>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">{{ $student->full_name_ar }}</p>
                </div>
            </div>

            <!-- English Name -->
            <div class="flex items-start gap-4 p-4 rounded-xl bg-gray-50 border border-gray-100 dark:bg-slate-800/50 dark:border-slate-700">
                <div class="mt-1">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">الاسم بالكامل (إنجليزي)</span>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">{{ $student->full_name_en }}</p>
                </div>
            </div>

            <!-- Date of Birth -->
            <div class="flex items-start gap-4 p-4 rounded-xl bg-gray-50 border border-gray-100 dark:bg-slate-800/50 dark:border-slate-700">
                <div class="p-2 bg-white rounded-lg shadow-sm text-gray-400 dark:bg-slate-700 dark:text-slate-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">تاريخ الميلاد</span>
                    <p class="mt-1 text-sm font-bold text-gray-900 dark:text-white dir-ltr">{{ $student->date_of_birth }}</p>
                </div>
            </div>

            <!-- National ID -->
            <div class="flex items-start gap-4 p-4 rounded-xl bg-gray-50 border border-gray-100 dark:bg-slate-800/50 dark:border-slate-700">
                <div class="p-2 bg-white rounded-lg shadow-sm text-gray-400 dark:bg-slate-700 dark:text-slate-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">الرقم القومي</span>
                    <p class="mt-1 text-sm font-bold text-gray-900 dark:text-white font-mono">{{ $student->national_id }}</p>
                </div>
            </div>
            
            <!-- Blood Type -->
             <div class="flex items-start gap-4 p-4 rounded-xl bg-gray-50 border border-gray-100 dark:bg-slate-800/50 dark:border-slate-700">
                <div class="p-2 bg-white rounded-lg shadow-sm text-rose-400 dark:bg-slate-700 dark:text-rose-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">فصيلة الدم</span>
                    <p class="mt-1 text-sm font-bold text-gray-900 dark:text-white">{{ $student->blood_type ?? '-' }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Details Section -->
    <section>
        <div class="flex items-center gap-3 mb-4 mt-8">
            <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg dark:bg-emerald-900/30 dark:text-emerald-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">معلومات الاتصال</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Email -->
             <div class="flex items-start gap-4 p-4 rounded-xl bg-gray-50 border border-gray-100 dark:bg-slate-800/50 dark:border-slate-700">
                <div class="p-2 bg-white rounded-lg shadow-sm text-gray-400 dark:bg-slate-700 dark:text-slate-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">البريد الإلكتروني</span>
                    <p class="mt-1 text-sm font-bold text-gray-900 dark:text-white">{{ $student->email ?? 'لا يوجد' }}</p>
                </div>
            </div>

            <!-- Phone -->
            <div class="flex items-start gap-4 p-4 rounded-xl bg-gray-50 border border-gray-100 dark:bg-slate-800/50 dark:border-slate-700">
                <div class="p-2 bg-white rounded-lg shadow-sm text-gray-400 dark:bg-slate-700 dark:text-slate-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">رقم الهاتف</span>
                    @if($student->phone)
                        <a href="tel:{{ $student->phone }}" class="block mt-1 text-sm font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400">{{ $student->phone }}</a>
                    @else
                        <p class="mt-1 text-sm font-bold text-gray-400">غير مسجل</p>
                    @endif
                </div>
            </div>

            <!-- Address -->
             <div class="md:col-span-2 flex items-start gap-4 p-4 rounded-xl bg-gray-50 border border-gray-100 dark:bg-slate-800/50 dark:border-slate-700">
                <div class="p-2 bg-white rounded-lg shadow-sm text-gray-400 dark:bg-slate-700 dark:text-slate-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">العنوان</span>
                    <p class="mt-1 text-sm font-bold text-gray-900 dark:text-white leading-relaxed">{{ $student->address ?? 'العنوان غير مسجل' }}</p>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="modern-card-elevated p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">إجراءات سريعة</h3>
        <div class="grid grid-cols-1 gap-3 text-sm">
            <a href="{{ route('students.register') }}" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500 transition">تسجيل طالب جديد</a>
            <a href="{{ route('teachers.create') }}" class="px-4 py-2 rounded-lg bg-slate-800 text-white hover:bg-slate-700 transition">إضافة معلم</a>
            <a href="{{ route('academic-years.index') }}" class="px-4 py-2 rounded-lg bg-slate-700 text-white hover:bg-slate-600 transition">إدارة السنوات الدراسية</a>
            <a href="{{ route('attendance.settings') }}" class="px-4 py-2 rounded-lg bg-slate-700 text-white hover:bg-slate-600 transition">إعدادات الحضور</a>
        </div>
    </div>

    <div class="modern-card-elevated p-6 lg:col-span-2">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">ملخص سريع للبيانات</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="rounded-xl border border-gray-200 dark:border-slate-700 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400">إجمالي الفواتير</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($finance['total'], 0) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-slate-700 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400">محصل</p>
                <p class="text-lg font-semibold text-emerald-500">{{ number_format($finance['paid'], 0) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-slate-700 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400">متبقي</p>
                <p class="text-lg font-semibold text-amber-500">{{ number_format($finance['outstanding'], 0) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-slate-700 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400">نسبة التحصيل</p>
                <p class="text-lg font-semibold text-indigo-500">{{ $finance['paid_ratio'] }}%</p>
            </div>
        </div>
    </div>
</div>

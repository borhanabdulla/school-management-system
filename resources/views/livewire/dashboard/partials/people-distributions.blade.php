<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
    <div class="modern-card-elevated p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">توزيع الطلاب حسب الصف</h3>
        <div class="space-y-3">
            @foreach ($gradeDistribution['items'] as $item)
                <div>
                    <div class="flex items-center justify-between text-sm text-gray-600 dark:text-slate-300 mb-1">
                        <span>{{ $item['name'] }}</span>
                        <span class="font-semibold">{{ $item['total'] }}</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700/60 rounded-full h-2">
                        <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ ($item['total'] / $gradeDistribution['max']) * 100 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="modern-card-elevated p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">حالة الموظفين</h3>
        <div class="space-y-3">
            @foreach ($staffBreakdown['items'] as $item)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-slate-300">{{ $item['label'] }}</span>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $pillMap[$item['color']] ?? 'bg-slate-500/15 text-slate-300' }}">
                        {{ $item['total'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="modern-card-elevated p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">أعلى عبء تدريسي</h3>
        <div class="space-y-3">
            @foreach ($topTeachers['items'] as $item)
                <div>
                    <div class="flex items-center justify-between text-sm text-gray-600 dark:text-slate-300 mb-1">
                        <span>{{ $item['name'] }}</span>
                        <span class="font-semibold">{{ $item['count'] }}</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700/60 rounded-full h-2">
                        <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ ($item['count'] / $topTeachers['max']) * 100 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

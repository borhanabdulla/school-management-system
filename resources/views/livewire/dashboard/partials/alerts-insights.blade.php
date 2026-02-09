<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
    <div class="modern-card-elevated p-6 lg:col-span-2">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">تنبيهات واحتياج متابعة</h3>
                <span class="text-xs text-gray-500 dark:text-slate-400">{{ count($alerts) }} تنبيه</span>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <button type="button" wire:click="$set('alertSeverity', 'all')"
                    class="px-3 py-1 rounded-full {{ $alertSeverity === 'all' ? 'bg-indigo-500 text-white' : 'bg-slate-100 dark:bg-slate-800/70 text-slate-600 dark:text-slate-300' }}">
                    الكل
                </button>
                <button type="button" wire:click="$set('alertSeverity', 'blocking')"
                    class="px-3 py-1 rounded-full {{ $alertSeverity === 'blocking' ? 'bg-red-500 text-white' : 'bg-slate-100 dark:bg-slate-800/70 text-slate-600 dark:text-slate-300' }}">
                    مانعة
                </button>
                <button type="button" wire:click="$set('alertSeverity', 'warning')"
                    class="px-3 py-1 rounded-full {{ $alertSeverity === 'warning' ? 'bg-amber-500 text-white' : 'bg-slate-100 dark:bg-slate-800/70 text-slate-600 dark:text-slate-300' }}">
                    تنبيهات
                </button>
            </div>
        </div>
        <div class="space-y-3">
            @forelse ($alerts as $alert)
                <div x-data="{ hidden: false }" x-show="!hidden"
                    class="flex items-start gap-3 rounded-xl border border-gray-200 dark:border-slate-700 p-3">
                    <div class="mt-1 w-2.5 h-2.5 rounded-full {{ $alert['type'] === 'blocking' ? 'bg-red-500' : 'bg-amber-400' }}"></div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $alert['title'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-slate-300">{{ $alert['message'] }}</p>
                        <div class="mt-3 flex flex-wrap gap-2 text-xs">
                            @if (!empty($alert['route']))
                                <a href="{{ route($alert['route'], $alert['route_params'] ?? []) }}"
                                    class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800/70 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                                    افتح الإعدادات
                                </a>
                            @endif
                            @if (!empty($alert['drawer']))
                                <button type="button" wire:click="openDrawer('{{ $alert['drawer'] }}')"
                                    class="px-2.5 py-1 rounded-full bg-indigo-500/10 text-indigo-500 hover:bg-indigo-500/20 transition">
                                    تفاصيل
                                </button>
                            @endif
                            <button type="button" @click="hidden = true"
                                class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800/70 text-slate-500 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                                تجاهل مؤقت
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-sm text-gray-500 dark:text-slate-300">لا توجد تنبيهات حالياً.</div>
            @endforelse
        </div>
    </div>

    <div class="modern-card-elevated p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">نبض اليوم</h3>
        <div class="space-y-4">
            @foreach ($insights as $insight)
                <div class="rounded-xl border border-gray-200 dark:border-slate-700 p-3">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full {{ $insight['type'] === 'blocking' ? 'bg-red-500' : ($insight['type'] === 'warning' ? 'bg-amber-400' : 'bg-emerald-400') }}"></span>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $insight['title'] }}</p>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-slate-300 mt-1">{{ $insight['message'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>

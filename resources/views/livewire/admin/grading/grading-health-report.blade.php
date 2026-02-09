<div class="space-y-4 bg-white dark:bg-gray-800/80 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-sm p-4">
    @php
        $isClean = empty($report['missing']) && empty($report['invalid']);
    @endphp
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="text-sm font-semibold uppercase tracking-wide text-gray-500">بوابة صحة التكوينات</div>
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">تقرير صحة SubjectGradingConfig</h3>
            <p class="text-xs text-gray-500">نفس الـ validators التي تمنع الحساب في الباكند.</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <select wire:model="termId" class="border rounded-lg px-3 py-2 text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700">
                <option value="">اختيار الترم</option>
                @foreach($terms as $term)
                    <option value="{{ $term['id'] }}">{{ $term['name'] }}</option>
                @endforeach
            </select>
            <button wire:click="refreshReport" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-bold transition">
                <span>تشغيل الفحص</span>
                <svg wire:loading class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-3">
            <div class="text-xs text-gray-500">مفحوص</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($report['checked']) }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-3">
            <div class="text-xs text-gray-500">مفقودة</div>
            <div class="text-2xl font-bold text-red-600">{{ count($report['missing']) }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-3">
            <div class="text-xs text-gray-500">غير صالحة</div>
            <div class="text-2xl font-bold text-amber-600">{{ count($report['invalid']) }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-3">
            <div class="text-xs text-gray-500">تحذيرات</div>
            <div class="text-2xl font-bold text-indigo-600">{{ count($report['warnings']) }}</div>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2 text-sm">
        <span class="font-semibold">الحالة:</span>
        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $isClean ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
            {{ $statusMessage ?: ($isClean ? 'لا توجد مشاكل حرجة' : 'هناك مشاكل بحاجة حل') }}
        </span>
    </div>

    @php
        $guardRoute = route('grading.settings', ['tab' => 'subjects']);
    @endphp

    <div class="space-y-4">
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold">الـ Missing</div>
                <div class="text-xs text-gray-400">{{ count($report['missing']) }}</div>
            </div>
            <div class="rounded-xl border border-dashed border-gray-200 dark:border-gray-700 p-3 space-y-2">
                @if(count($report['missing']) === 0)
                    <p class="text-xs text-gray-400">لا توجد تكوينات مفقودة.</p>
                @else
                    <ul class="space-y-2">
                        @foreach($report['missing'] as $row)
                            <li class="flex items-center justify-between text-sm">
                                <div class="space-y-1">
                                    <div class="font-semibold text-gray-900 dark:text-white">Subject #{{ $row['subject_id'] ?? '—' }}</div>
                                    <div class="text-xs text-gray-500">Grade #{{ $row['grade_id'] ?? '—' }} · Offering #{{ $row['course_offering_id'] }}</div>
                                </div>
                                <a href="{{ $guardRoute }}"
                                   class="text-purple-600 hover:underline text-xs font-bold">
                                    فتح التكوين
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold">الـ Invalid</div>
                <div class="text-xs text-gray-400">{{ count($report['invalid']) }}</div>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50/60 p-3 space-y-2">
                @if(count($report['invalid']) === 0)
                    <p class="text-xs text-red-600">لا توجد مخالفات تمنع الحساب.</p>
                @else
                    <ul class="space-y-2">
                        @foreach($report['invalid'] as $row)
                            <li class="flex items-start justify-between gap-3 rounded-lg border border-red-100 bg-white/60 p-3">
                                <div>
                                    <div class="text-sm font-semibold text-red-700">{{ $row['violation']['message'] }}</div>
                                    <div class="text-xs text-gray-500">Type: {{ $row['violation']['type'] }} · Subject #{{ $row['subject_id'] ?? '—' }} · Grade #{{ $row['grade_id'] ?? '—' }}</div>
                                </div>
                                <div class="flex flex-col items-end gap-1 text-xs">
                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full uppercase">{{ $row['violation']['severity'] }}</span>
                                    <a href="{{ $guardRoute }}" class="text-purple-600 hover:underline">راجع</a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold">الـ Warnings</div>
                <div class="text-xs text-gray-400">{{ count($report['warnings']) }}</div>
            </div>
            <div class="rounded-xl border border-indigo-200 bg-indigo-50/60 p-3 space-y-2">
                @if(count($report['warnings']) === 0)
                    <p class="text-xs text-indigo-600">لا توجد تحذيرات تُستدعى.</p>
                @else
                    <ul class="space-y-2">
                        @foreach($report['warnings'] as $row)
                            <li class="flex items-center justify-between gap-3 rounded-lg border border-indigo-100 bg-white/60 p-3 text-sm">
                                <div>
                                    <div class="font-semibold">{{ $row['violation']['message'] }}</div>
                                    <div class="text-xs text-gray-500">Type: {{ $row['violation']['type'] }}</div>
                                </div>
                                <a href="{{ $guardRoute }}" class="text-purple-600 hover:underline text-xs">فتح</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

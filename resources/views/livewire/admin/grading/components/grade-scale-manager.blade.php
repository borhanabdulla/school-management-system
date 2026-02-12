<div class="max-w-4xl mx-auto space-y-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-100 dark:bg-purple-900/40">
                <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
            </div>
            <div>
                <div class="text-base font-bold text-gray-800 dark:text-white">سلم التقديرات</div>
                <div class="text-xs text-gray-500 dark:text-slate-400">حدد التقديرات ونسبها واللون لكل مستوى</div>
            </div>
        </div>

        <button wire:click="save"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-l from-purple-600 to-purple-700 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-purple-500/20 transition-all hover:shadow-lg hover:-translate-y-0.5 disabled:opacity-50">
            <span wire:loading.remove>حفظ التغييرات</span>
            <span wire:loading>جارٍ الحفظ...</span>
        </button>
    </div>

    @if($externalError)
        <x-grading.help-hint title="خطأ" :message="$externalError" variant="danger" :dismissible="false" />
    @endif

    @error('gradeScale')
        <x-grading.help-hint title="خطأ" :message="$message" variant="danger" :dismissible="false" />
    @enderror

    {{-- Grade scale cards --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($gradeScale as $index => $scale)
            @php
                $scaleColor = $scale['color'] ?? '#6b7280';
            @endphp
            <div class="group rounded-2xl border border-gray-200/60 bg-white/95 p-4 shadow-sm transition-all duration-200 hover:shadow-md dark:border-slate-700/50 dark:bg-slate-900/80">
                {{-- Color preview header --}}
                <div class="mb-3 flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl shadow-inner transition-all duration-300" style="background-color: {{ $scaleColor }}"></div>
                    <div class="flex-1">
                        <input type="text" wire:model="gradeScale.{{ $index }}.grade"
                               class="w-full rounded-lg border-0 bg-transparent px-0 text-lg font-black text-gray-800 focus:ring-0 dark:text-white"
                               placeholder="التقدير">
                        <div class="mt-1 text-[10px] text-gray-400">رمز التقدير الذي يظهر في التقارير.</div>
                    </div>
                    <input type="color" wire:model="gradeScale.{{ $index }}.color"
                           class="h-8 w-8 cursor-pointer rounded-lg border-0 bg-transparent p-0">
                </div>

                {{-- Percentage range --}}
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="mb-1 block text-[10px] font-semibold text-gray-400 dark:text-slate-500">من %</label>
                        <input type="number" wire:model="gradeScale.{{ $index }}.min"
                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-center text-sm shadow-sm focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200">
                        <div class="mt-1 text-[10px] text-gray-400">بداية نطاق هذه الدرجة.</div>
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-semibold text-gray-400 dark:text-slate-500">إلى %</label>
                        <input type="number" wire:model="gradeScale.{{ $index }}.max"
                               class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-center text-sm shadow-sm focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200">
                        <div class="mt-1 text-[10px] text-gray-400">نهاية نطاق هذه الدرجة.</div>
                    </div>
                </div>

                {{-- Visual range bar --}}
                @php
                    $minVal = (float) ($scale['min'] ?? 0);
                    $maxVal = (float) ($scale['max'] ?? 100);
                @endphp
                <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-slate-800">
                    <div class="h-1.5 rounded-full transition-all duration-500" style="background-color: {{ $scaleColor }}; width: {{ $maxVal }}%; margin-right: {{ 100 - $maxVal }}%"></div>
                </div>
                <div class="mt-1 text-center text-[10px] text-gray-400">{{ number_format($minVal) }}% — {{ number_format($maxVal) }}%</div>
            </div>
        @endforeach
    </div>
</div>

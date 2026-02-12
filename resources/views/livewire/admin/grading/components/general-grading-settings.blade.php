<div class="max-w-2xl mx-auto space-y-6">
    {{-- ═══ Pass Score & Grace ═══ --}}
    <div class="rounded-2xl border border-gray-200/60 bg-white/95 p-5 shadow-sm dark:border-slate-700/50 dark:bg-slate-900/80">
        <div class="mb-4 flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/40">
                <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div>
                <div class="text-sm font-bold text-gray-800 dark:text-white">قواعد النجاح والرأفة</div>
                <div class="text-[11px] text-gray-500 dark:text-slate-400">حدد درجة النجاح الأساسية وحدود الرأفة</div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold text-gray-500 dark:text-slate-400">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                    </svg>
                    درجة النجاح الافتراضية
                </label>
                <input type="number" wire:model="defaultPassScore"
                       class="w-full rounded-xl border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm transition focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200">
                <div class="mt-1 text-[10px] text-gray-400">أقل نسبة نجاح للمادة إذا لم تحدد لها درجة نجاح خاصة.</div>
                @error('defaultPassScore')
                    <span class="mt-1 text-[11px] text-rose-600">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold text-gray-500 dark:text-slate-400">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                    </svg>
                    حد درجات الرأفة
                </label>
                <input type="number" wire:model="graceMarksLimit"
                       class="w-full rounded-xl border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm transition focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200">
                <div class="mt-1 text-[10px] text-gray-400">عدد درجات الرأفة القصوى التي يمكن إضافتها تلقائياً.</div>
                @error('graceMarksLimit')
                    <span class="mt-1 text-[11px] text-rose-600">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    {{-- ═══ Term Weights ═══ --}}
    <div class="rounded-2xl border border-gray-200/60 bg-white/95 p-5 shadow-sm dark:border-slate-700/50 dark:bg-slate-900/80">
        <div class="mb-4 flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/40">
                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971Z" />
                </svg>
            </div>
            <div>
                <div class="text-sm font-bold text-gray-800 dark:text-white">أوزان الفصول الدراسية</div>
                <div class="text-[11px] text-gray-500 dark:text-slate-400">يجب أن يكون المجموع = 100%</div>
            </div>
        </div>

        @php
            $terms = app(\App\Domains\Academic\Term\Services\TermLookupService::class)->getActiveTerms();
            $totalWeight = array_sum($termWeights);
            $isValid = $totalWeight == 100;
        @endphp

        <div class="space-y-3">
            @foreach($terms as $term)
                <div class="flex items-center justify-between rounded-xl bg-gray-50/80 px-4 py-3 dark:bg-slate-800/40">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $term->name }}</span>
                    <div class="flex items-center gap-2">
                        <div class="flex flex-col items-end">
                            <input type="number" wire:model="termWeights.{{ $term->id }}"
                                   class="w-20 rounded-xl border-gray-200 bg-white px-3 py-2 text-center text-sm shadow-sm transition focus:border-purple-400 focus:ring-purple-400/20 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200">
                            <span class="mt-1 text-[10px] text-gray-400">نسبة هذا الترم</span>
                        </div>
                        <span class="text-xs text-gray-400">%</span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Weight progress --}}
        <div class="mt-4 rounded-xl border {{ $isValid ? 'border-emerald-200/60 bg-emerald-50/40 dark:border-emerald-700/40 dark:bg-emerald-900/15' : 'border-rose-200/60 bg-rose-50/40 dark:border-rose-700/40 dark:bg-rose-900/15' }} p-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs font-semibold {{ $isValid ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">
                    @if($isValid)
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    @else
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    @endif
                    {{ $isValid ? 'المجموع صحيح' : 'المجموع لا يساوي 100%' }}
                </div>
                <span class="text-sm font-black {{ $isValid ? 'text-emerald-600' : 'text-rose-600' }}">{{ $totalWeight }}%</span>
            </div>
            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-slate-800">
                <div class="h-1.5 rounded-full transition-all duration-700 {{ $isValid ? 'bg-emerald-500' : 'bg-rose-500' }}" style="width: {{ min(100, $totalWeight) }}%"></div>
            </div>
        </div>

        @if($externalError)
            <div class="mt-3 text-xs text-rose-600">{{ $externalError }}</div>
        @endif
        @error('termWeights')
            <div class="mt-3 text-xs text-rose-600">{{ $message }}</div>
        @enderror
    </div>

    {{-- Save button --}}
    <div class="flex justify-end">
        <button wire:click="save"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-l from-purple-600 to-purple-700 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-purple-500/20 transition-all hover:shadow-xl hover:shadow-purple-500/30 hover:-translate-y-0.5 disabled:opacity-50">
            <span wire:loading.remove>حفظ الإعدادات العامة</span>
            <span wire:loading>جارٍ الحفظ...</span>
        </button>
    </div>
</div>

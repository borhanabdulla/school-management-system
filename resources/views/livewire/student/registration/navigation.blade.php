<div class="mt-8 pt-6 border-t border-white/10 dark:border-gray-700/50 flex justify-between items-center">
    @if ($currentStep > 1)
        <button wire:click="previousStep"
            class="px-8 py-3 rounded-xl border border-white/10 text-white hover:bg-white/10 font-bold transition-all flex items-center gap-2 backdrop-blur-sm group">
            <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            السابق
        </button>
    @else
        <div></div>
    @endif

    @if ($currentStep < 5)
        <button wire:click="nextStep"
            class="px-10 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold hover:shadow-lg hover:shadow-blue-500/40 transition-all transform hover:-translate-y-1 flex items-center gap-3 group">
            التالي
            <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
    @else
        <button wire:click="submit"
            class="px-10 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold hover:shadow-lg hover:shadow-emerald-500/40 transition-all transform hover:-translate-y-1 flex items-center gap-3 group">
            <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            تأكيد التسجيل
        </button>
    @endif
</div>

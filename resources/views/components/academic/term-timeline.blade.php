@props(['terms'])

<div class="relative py-8">
    {{-- خط الربط الخلفي --}}
    <div
        class="absolute top-1/2 left-0 right-0 h-1 bg-border dark:bg-gray-700 -translate-y-1/2 z-0 hidden md:block rounded-full mx-12 transition-colors duration-300">
    </div>

    <div class="grid grid-cols-1 md:grid-flow-col md:auto-cols-fr gap-8 relative z-10">
        @forelse($terms as $term)
            <div class="relative">
                {{-- Date Marker --}}
                <div class="hidden md:block absolute -top-16 left-1/2 -translate-x-1/2 text-center">
                    <span
                        class="text-xs font-bold text-secondary bg-surface px-2 py-1 rounded-full border border-border shadow-sm">
                        {{ \App\Infrastructure\Support\Helpers::formatDate($term->start_date) }}
                    </span>
                </div>

                <x-academic.term-card :term="$term" />
            </div>
        @empty
            <x-academic.empty-state title="لا توجد فصول دراسية" message="لا توجد فصول دراسية لهذه السنة."
                action="إضافة الفصل الأول" wire:click="create" />
        @endforelse
    </div>
</div>

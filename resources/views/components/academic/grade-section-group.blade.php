@props(['grade'])

<div class="bg-surface rounded-2xl shadow-sm border border-border overflow-hidden">
    {{-- Grade Header --}}
    <div class="bg-background px-6 py-4 border-b border-border flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-lg bg-surface border border-border flex items-center justify-center font-bold text-foreground shadow-sm">
                {{ $grade->level_order }}
            </div>
            <div>
                <h3 class="text-lg font-bold text-foreground">{{ $grade->name }}</h3>
                <p class="text-xs text-muted-foreground">{{ $grade->stage->name }} • {{ $grade->sections->count() }} شعب دراسية
                </p>
            </div>
        </div>
        {{-- Quick Add Button --}}
        <button wire:click="create"
            class="text-xs bg-surface border border-border hover:bg-background text-muted-foreground px-3 py-1.5 rounded-lg transition shadow-sm">
            + إضافة شعبة
        </button>
    </div>

    {{-- Sections Grid --}}
    <div class="p-6">
        @if ($grade->sections->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($grade->sections as $section)
                    @php
                        $genderColor = match ($section->gender_type) {
                            'boys' => 'blue',
                            'girls' => 'pink',
                            'mixed' => 'purple',
                            default => 'gray',
                        };
                    @endphp
                    <x-academic.section-card :section="$section" :genderColor="$genderColor" />
                @endforeach
            </div>
        @else
            <div class="text-center py-10 border-2 border-dashed border-border rounded-xl bg-background/50">
                <p class="text-muted-foreground text-sm">لا توجد شعب دراسية لهذا الصف في السنة المحددة.</p>
                <button wire:click="create" class="text-blue-600 font-bold text-sm mt-2 hover:underline">إضافة أول
                    شعبة</button>
            </div>
        @endif
    </div>
</div>

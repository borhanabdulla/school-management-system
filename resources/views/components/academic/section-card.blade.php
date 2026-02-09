@props(['section', 'genderColor'])

@php
    $isActive = $section->is_active;
    $genderLabels = [
        'boys' => 'بنين 👨‍🎓',
        'girls' => 'بنات 👩‍🎓',
        'mixed' => 'مختلط 👫',
    ];
@endphp

<div
    class="group relative bg-surface rounded-xl border-2 {{ $isActive ? 'border-border hover:border-' . $genderColor . '-200' : 'border-border bg-background opacity-75' }} shadow-sm hover:shadow-md transition-all duration-300">
    {{-- Top Stripe --}}
    <div class="h-1.5 w-full rounded-t-xl bg-{{ $genderColor }}-500"></div>

    <div class="p-5">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h4 class="text-xl font-bold text-foreground">{{ $section->name }}</h4>
                <span
                    class="text-xs text-{{ $genderColor }}-600 bg-{{ $genderColor }}-50 px-2 py-0.5 rounded-md font-medium mt-1 inline-block">
                    {{ $genderLabels[$section->gender_type->value] ?? 'غير محدد' }}
                </span>
            </div>
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                    class="text-muted-foreground hover:text-foreground p-1 rounded-full hover:bg-background transition">
                    <span class="inline-flex items-center justify-center w-5 h-5">
                        @include('icons.dots-vertical')
                    </span>
                </button>
                <div x-show="open" @click.away="open = false"
                    class="absolute left-0 mt-2 w-32 bg-surface rounded-xl shadow-lg border border-border z-10 py-1"
                    style="display: none;">
                    <button wire:click="edit({{ $section->id }})"
                        class="flex w-full items-center px-4 py-2 text-sm text-foreground hover:bg-background">تعديل</button>
                    <button wire:click="delete({{ $section->id }})" wire:confirm="حذف الشعبة؟"
                        class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">حذف</button>
                </div>
            </div>
        </div>

        {{-- Capacity Bar --}}
        <div class="mb-2">
            <div class="flex justify-between text-xs text-muted-foreground mb-1">
                <span>السعة</span>
                <span class="font-bold text-foreground">{{ $section->max_capacity }}</span>
            </div>
            <div class="w-full h-2 bg-border rounded-full overflow-hidden">
                {{-- Placeholder for future enrollment percentage --}}
                <div class="h-full bg-muted-foreground/30 w-0"></div>
            </div>
        </div>

        {{-- Status --}}
        <div class="flex items-center justify-between mt-4 pt-4 border-t border-border/50">
            <span class="text-xs {{ $isActive ? 'text-green-600' : 'text-red-500' }} flex items-center gap-1">
                <span class="w-2 h-2 rounded-full {{ $isActive ? 'bg-green-500' : 'bg-red-500' }}"></span>
                {{ $isActive ? 'نشطة' : 'معطلة' }}
            </span>
        </div>
    </div>
</div>

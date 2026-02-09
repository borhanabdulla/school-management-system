@props(['allYears', 'allGrades', 'filterYear', 'filterGrade', 'search'])

<div class="bg-surface p-4 rounded-2xl shadow-sm border border-border flex flex-col md:flex-row gap-4 items-center">
    <div class="w-full md:w-1/4">
        <label class="block text-xs font-bold text-muted-foreground mb-1">السنة الدراسية</label>
        <select wire:model.live="filterYear"
            class="w-full bg-background border-none rounded-xl text-sm font-bold text-foreground focus:ring-2 focus:ring-blue-500/20">
            <option value="">-- كل السنوات --</option>
            @foreach ($allYears as $y)
                <option value="{{ $y->id }}">{{ $y->name }}
                    {{ $y->status === \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Active ? '(النشطة)' : '' }}</option>
            @endforeach
        </select>
    </div>
    <div class="w-full md:w-1/4">
        <label class="block text-xs font-bold text-muted-foreground mb-1">تصفية حسب الصف</label>
        <select wire:model.live="filterGrade"
            class="w-full bg-background border-none rounded-xl text-sm font-bold text-foreground focus:ring-2 focus:ring-blue-500/20">
            <option value="">-- كل الصفوف --</option>
            @foreach ($allGrades as $g)
                <option value="{{ $g->id }}">{{ $g->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="w-full md:w-1/2">
        <label class="block text-xs font-bold text-muted-foreground mb-1">بحث سريع</label>
        <div class="relative">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="بحث عن اسم الشعبة..."
                class="w-full bg-background border-none rounded-xl text-sm pl-10 focus:ring-2 focus:ring-blue-500/20">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="inline-flex items-center justify-center w-5 h-5 text-muted-foreground">
                    @include('icons.search')
                </span>
            </div>
        </div>
    </div>
</div>

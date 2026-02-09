<div class="min-h-screen bg-gradient-to-br py-8 px-4 sm:px-6 lg:px-8 transition-colors duration-500"
    :class="currentTheme.bg"
    x-data="{ 
        search: '', 
        activeStage: 'all',
        
        themes: {
            'all': { 
                bg: 'from-background to-blue-50/30 dark:from-background dark:to-surface', 
                header: 'from-indigo-600 to-purple-700',
                icon: 'text-indigo-400'
            },
            1: { 
                bg: 'from-emerald-50 to-teal-50/30 dark:from-background dark:to-surface', 
                header: 'from-emerald-600 to-teal-700',
                icon: 'text-emerald-400'
            },
            2: { 
                bg: 'from-blue-50 to-indigo-50/30 dark:from-background dark:to-surface', 
                header: 'from-blue-600 to-indigo-700',
                icon: 'text-blue-400'
            },
            3: { 
                bg: 'from-purple-50 to-fuchsia-50/30 dark:from-background dark:to-surface', 
                header: 'from-purple-600 to-fuchsia-700',
                icon: 'text-purple-400'
            }
        },

        get currentTheme() {
            return this.themes[this.activeStage] || this.themes['all'];
        },

        // Filter Logic
        isVisible(gradeName, stageId) {
            const matchesSearch = this.search === '' || gradeName.toLowerCase().includes(this.search.toLowerCase());
            const matchesStage = this.activeStage === 'all' || this.activeStage === stageId;
            return matchesSearch && matchesStage;
        }
    }">
    
    <div class="max-w-7xl mx-auto space-y-8">
        {{-- 1. Header & Stats --}}
        <div class="relative overflow-hidden bg-gradient-to-r rounded-2xl shadow-xl p-8 text-white transition-all duration-500"
             :class="currentTheme.header">
            <div class="absolute top-0 right-0 w-32 h-32 bg-surface/10 rounded-full -translate-y-16 translate-x-16"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-surface/5 rounded-full translate-y-12 -translate-x-12"></div>

            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-surface/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h1 class="text-3xl font-bold">الدليل الأكاديمي</h1>
                    </div>
                    <p class="text-indigo-100 max-w-md text-lg">مركز القيادة والتحكم للمنظومة التعليمية</p>
                </div>

                <div class="flex items-center gap-4 bg-surface/10 p-2 rounded-xl backdrop-blur-sm border border-border/10">
                    <div class="text-center px-4 border-l border-border/20">
                        <span class="block text-2xl font-bold">{{ $stats['grades_count'] }}</span>
                        <span class="text-xs text-indigo-100">صفوف</span>
                    </div>
                    <div class="text-center px-4 border-l border-border/20">
                        <span class="block text-2xl font-bold">{{ $stats['sections_count'] }}</span>
                        <span class="text-xs text-indigo-100">شعب</span>
                    </div>
                    <div class="text-center px-4">
                        <span class="block text-2xl font-bold">{{ $stats['students_count'] }}</span>
                        <span class="text-xs text-indigo-100">طلاب</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Controls (Search & Filter) --}}
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-surface p-4 rounded-2xl shadow-sm border border-border">
            {{-- Stage Filter --}}
            <div class="flex flex-wrap gap-2">
                <button @click="activeStage = 'all'"
                    :class="{ 'bg-foreground text-[rgb(var(--color-white))] shadow-md': activeStage === 'all', 'bg-background text-muted-foreground hover:bg-border': activeStage !== 'all' }"
                    class="px-5 py-2.5 rounded-xl font-bold text-sm transition-all">
                    الكل
                </button>
                @foreach ($stages as $stage)
                    @php
                        $colors = match ($stage->rank) {
                            1 => 'emerald',
                            2 => 'blue',
                            3 => 'purple',
                            default => 'gray',
                        };
                    @endphp
                    <button @click="activeStage = {{ $stage->id }}"
                        :class="{ 
                            'bg-{{ $colors }}-600 text-white shadow-md': activeStage === {{ $stage->id }},
                            'bg-{{ $colors }}-50 text-{{ $colors }}-700 hover:bg-{{ $colors }}-100': activeStage !== {{ $stage->id }}
                        }"
                        class="px-5 py-2.5 rounded-xl font-bold text-sm transition-all flex items-center gap-2">
                        <span>{{ str_replace('المرحلة ', '', $stage->name) }}</span>
                    </button>
                @endforeach
            </div>

            {{-- Search --}}
            <div class="relative w-full md:w-72">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input x-model="search" type="text" placeholder="بحث سريع..."
                    class="w-full bg-background border-none rounded-xl py-2.5 pr-10 pl-4 text-foreground placeholder:text-muted-foreground focus:ring-2 focus:ring-indigo-500/20 transition-all">
            </div>
        </div>

        {{-- 3. Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse ($grades as $grade)
                <div x-show="isVisible('{{ $grade->name }}', {{ $grade->educational_stage_id }})"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100">
                    <x-academic.grade-card :grade="$grade" />
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <div class="w-16 h-16 bg-background rounded-full flex items-center justify-center mx-auto mb-4 transition-colors duration-500"
                         :class="currentTheme.icon.replace('text-', 'text-').replace('400', '100')"> {{-- Using lighter shade for bg --}}
                        <svg class="w-8 h-8" :class="currentTheme.icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-foreground">لا توجد نتائج</h3>
                </div>
            @endforelse
        </div>
    </div>
</div>

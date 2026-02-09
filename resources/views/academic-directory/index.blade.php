@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php
                $missingRequirements = [];
                if (!$hasStages) {
                    $missingRequirements[] = ['name' => 'المراحل الدراسية', 'route' => 'structure.index'];
                }
                if (!$hasAcademicYears) {
                    $missingRequirements[] = ['name' => 'السنوات الدراسية', 'route' => 'academic-years.index'];
                }
                $canProceed = empty($missingRequirements);
            @endphp

            @if (!$canProceed)
                {{-- رسالة تحذيرية --}}
                <div
                    class="mb-6 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border-r-4 border-amber-500 rounded-xl p-6 shadow-soft">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-amber-800 dark:text-amber-300 mb-2">
                                متطلبات ناقصة
                            </h3>
                            <p class="text-amber-700 dark:text-amber-400 mb-4">
                                لعرض الدليل الأكاديمي، يجب إنشاء العناصر التالية أولاً:
                            </p>

                            <div class="space-y-3">
                                @foreach ($missingRequirements as $requirement)
                                    <div class="flex items-center gap-3 bg-surface/50 rounded-lg p-3">
                                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        <span class="flex-1 text-amber-700 dark:text-amber-400 font-medium">
                                            {{ $requirement['name'] }}
                                        </span>
                                        <a href="{{ route($requirement['route']) }}"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg transition-all duration-200">
                                            انتقل
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- المكون الأساسي --}}
                <livewire:academic.academic-directory-manager />
            @endif
        </div>
    </div>
@endsection

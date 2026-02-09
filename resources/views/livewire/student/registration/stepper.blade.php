<div class="mb-12 relative z-10">
    <!-- Desktop Stepper -->
    <div class="hidden md:flex items-center justify-between max-w-4xl mx-auto">
        @php
            $steps = [
                1 => [
                    'label' => 'بيانات الطالب',
                    'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                ],
                2 => [
                    'label' => 'أولياء الأمور',
                    'icon' =>
                        'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                ],
                3 => [
                    'label' => 'الأكاديمية',
                    'icon' =>
                        'M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z',
                ],
                4 => [
                    'label' => 'المستندات',
                    'icon' =>
                        'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                ],
                5 => [
                    'label' => 'المالية',
                    'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                ],
                6 => ['label' => 'التأكيد', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ];
        @endphp

        @foreach ($steps as $stepNum => $step)
            <div class="flex items-center flex-1 {{ $stepNum < 6 ? 'relative' : '' }}">
                <div class="relative flex flex-col items-center z-20 group cursor-default">
                    <div
                        class="w-14 h-14 rounded-full flex items-center justify-center text-xl font-bold transition-all duration-500 border-4 
                            {{ $currentStep > $stepNum 
                                ? 'bg-emerald-500 border-emerald-500 text-white shadow-[0_0_20px_rgba(16,185,129,0.5)]' 
                                : ($currentStep == $stepNum 
                                    ? 'bg-background dark:bg-gray-800 border-indigo-600 dark:border-indigo-400 text-indigo-600 dark:text-indigo-400 shadow-lg scale-110' 
                                    : 'bg-gray-100 dark:bg-white/10 border-gray-200 dark:border-white/20 text-gray-500 dark:text-white/40') }}"

                        @if ($currentStep > $stepNum)
                            <svg class="w-7 h-7 animate-bounce-short" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                        @else
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="{{ $step['icon'] }}"></path>
                            </svg>
                        @endif
                    </div>

                    <span
                        class="mt-3 text-sm font-medium transition-all duration-300 {{ $currentStep >= $stepNum ? 'text-gray-900 dark:text-white translate-y-0 opacity-100' : 'text-gray-500 dark:text-white/40 translate-y-1 opacity-70' }}">
                        {{ $step['label'] }}
                    </span>
                </div>

                @if ($stepNum < 6)
                    <div class="flex-1 h-1 mx-4 relative rounded-full overflow-hidden bg-gray-200 dark:bg-white/10">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 to-teal-400 transition-all duration-1000 ease-out"
                            style="width: {{ $currentStep > $stepNum ? '100%' : '0%' }}"></div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Mobile Stepper -->
    <div class="md:hidden">
        <div
            class="flex items-center justify-between bg-white dark:bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-gray-200 dark:border-white/10 shadow-sm dark:shadow-none">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold shadow-lg ring-2 ring-white/20">
                    {{ $currentStep }}
                </div>
                <div>
                    <h3 class="text-gray-900 dark:text-white font-bold text-lg">{{ $steps[$currentStep]['label'] }}</h3>
                    <p class="text-gray-500 dark:text-white/60 text-xs">الخطوة {{ $currentStep }} من 6</p>
                </div>
            </div>
            <div class="w-12 h-12 relative">
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="4"
                        fill="transparent" class="text-gray-200 dark:text-white/10" />
                    <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="4"
                        fill="transparent" class="text-emerald-500 dark:text-emerald-400 transition-all duration-1000"
                        stroke-dasharray="{{ 2 * pi() * 20 }}"
                        stroke-dashoffset="{{ 2 * pi() * 20 * (1 - $currentStep / 6) }}" />
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="min-h-screen relative font-sans overflow-hidden" x-data="{ showMobileSteps: false }">
    <!-- Premium Background -->
    <!-- Premium Background -->
    <div class="fixed inset-0 -z-10 bg-background">
        <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 mix-blend-soft-light"></div>
        <div class="absolute inset-0 bg-gradient-to-tr from-primary/5 via-transparent to-secondary/5 animate-pulse slow"></div>
        
        <!-- Animated Orbs -->
        <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-primary/10 rounded-full blur-3xl animate-blob"></div>
        <div class="absolute top-[20%] right-[-10%] w-96 h-96 bg-secondary/10 rounded-full blur-3xl animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-[-10%] left-[20%] w-96 h-96 bg-accent/10 rounded-full blur-3xl animate-blob animation-delay-4000"></div>
    </div>

    <!-- Main Container -->
    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Header -->
        <div class="text-center mb-12 animate-fade-in-down">
            <div class="inline-flex justify-center mb-6">
                <div
                    class="p-4 bg-surface/50 backdrop-blur-md rounded-2xl border border-border shadow-2xl shadow-black/5 ring-1 ring-border/50">
                    <svg class="w-12 h-12 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
            </div>
            <h1 class="text-4xl lg:text-5xl font-bold text-foreground tracking-tight mb-4 drop-shadow-sm">
                تسجيل طالب جديد
            </h1>
            <p class="text-muted-foreground text-lg max-w-2xl mx-auto font-light leading-relaxed">
                انضم إلى عائلتنا الأكاديمية المتميزة وابدأ رحلة تعليمية ملهمة
            </p>
        </div>

        <!-- Stepper -->
        @include('livewire.student.registration.stepper')

        <!-- Glassmorphism Card -->
        <div
            class="backdrop-blur-xl bg-surface/80 border border-border rounded-3xl shadow-2xl shadow-black/5 overflow-hidden relative transition-all duration-500">

            <!-- Content Area -->
            <div class="p-8 lg:p-12 relative z-10">
                {{-- الخطوة 1: الطالب + السجل الطبي --}}
                @if ($currentStep === 1)
                    @include('livewire.student.registration.steps.1-student-info')

                    {{-- الخطوة 2: أولياء الأمور --}}
                @elseif ($currentStep === 2)
                    @include('livewire.student.registration.steps.2-guardians')

                    {{-- الخطوة 3: أكاديمي + سجل سابق --}}
                @elseif ($currentStep === 3)
                    @include('livewire.student.registration.steps.3-academic')

                    {{-- الخطوة 4: المرفقات --}}
                @elseif ($currentStep === 4)
                    @include('livewire.student.registration.steps.4-documents')

                    {{-- الخطوة 5: المالية (الجديدة) --}}
                @elseif ($currentStep === 5)
                    @include('livewire.student.registration.steps.5-financials')

                    {{-- الخطوة 6: المراجعة النهائية --}}
                @elseif ($currentStep === 6)
                    @include('livewire.student.registration.steps.6-review-submit')
                @endif

                <!-- Navigation Buttons -->
                <div class="mt-8 flex justify-between items-center border-t border-white/10 pt-6">

                    {{-- زر السابق --}}
                    @if ($currentStep > 1)
                        <button wire:click="previousStep" type="button" wire:loading.attr="disabled"
                            class="px-6 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white hover:bg-white/10 transition-all flex items-center gap-2 group disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7"></path>
                            </svg>
                            السابق
                        </button>
                    @else
                        <div></div> {{-- Spacer --}}
                    @endif

                    {{-- زر التالي / حفظ --}}
                    @if ($currentStep < 6)
                        <button wire:click="nextStep" type="button" wire:loading.attr="disabled"
                            wire:loading.class="opacity-75 cursor-wait"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:scale-105 transition-all flex items-center gap-2 group">
                            <span wire:loading.remove>التالي</span>
                            <span wire:loading>جاري المعالجة...</span>
                            <svg wire:loading.remove
                                class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                            <svg wire:loading class="w-4 h-4 animate-spin" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    @else
                        {{-- زر الحفظ النهائي --}}
                        <button wire:click="submit" wire:loading.attr="disabled" type="button"
                            class="px-8 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:scale-105 transition-all flex items-center gap-2">
                            <span wire:loading.remove>إتمام التسجيل وإنشاء الملف</span>
                            <span wire:loading>جاري المعالجة...</span>
                            <svg wire:loading.remove class="w-5 h-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </button>
                    @endif
                </div>
                <!-- Errors Display -->
                @if ($errors->any())
                    <div
                        class="mt-8 p-4 bg-red-500/10 backdrop-blur-md rounded-2xl border border-red-500/20 animate-fade-in">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-red-500/20 rounded-full">
                                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h4 class="font-bold text-red-200">يرجى مراجعة البيانات التالية</h4>
                        </div>
                        <ul class="list-disc list-inside text-sm text-red-300/90 space-y-1 pr-10">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        <!-- Footer Info -->
        <div class="text-center mt-8 text-muted-foreground text-sm font-light">
            &copy; {{ date('Y') }} نظام إدارة المدرسة الذكي. جميع الحقوق محفوظة.
        </div>
    </div>

</div>

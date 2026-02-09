<div class="min-h-screen relative font-sans overflow-hidden">
    {{-- Premium Background --}}
    <div class="fixed inset-0 -z-10">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-950 via-purple-900 to-blue-900 dark:from-gray-950 dark:via-gray-900 dark:to-black transition-colors duration-700"></div>
        <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 dark:opacity-10 mix-blend-soft-light"></div>
        <div class="absolute inset-0 bg-gradient-to-tr from-purple-500/10 via-transparent to-blue-600/10 animate-pulse slow"></div>

        {{-- Animated Orbs --}}
        <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-purple-500/20 rounded-full blur-3xl animate-blob"></div>
        <div class="absolute top-[20%] right-[-10%] w-96 h-96 bg-blue-500/20 rounded-full blur-3xl animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-[-10%] left-[20%] w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl animate-blob animation-delay-4000"></div>
    </div>

    {{-- Main Container --}}
    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- Header --}}
        <div class="text-center mb-12 animate-fade-in-down">
            <div class="inline-flex justify-center mb-6">
                <div class="p-4 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 shadow-2xl shadow-black/10 ring-1 ring-white/10">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <h1 class="text-4xl lg:text-5xl font-bold text-white tracking-tight mb-4 drop-shadow-lg">
                إضافة معلم جديد
            </h1>
            <p class="text-blue-100/80 text-lg max-w-2xl mx-auto font-light leading-relaxed">
                قم بإضافة معلم جديد إلى الهيئة التعليمية بكل سهولة ويسر
            </p>
        </div>

        {{-- Back Button --}}
        <div class="mb-6">
            <a href="{{ route('teachers.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl text-white hover:bg-white/10 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                العودة للقائمة
            </a>
        </div>

        {{-- Glassmorphism Card --}}
        <div class="backdrop-blur-xl bg-white/10 dark:bg-gray-900/40 border border-white/20 dark:border-gray-700/30 rounded-3xl shadow-2xl shadow-black/20 overflow-hidden relative transition-all duration-500">

            {{-- Content Area --}}
            <div class="p-8 lg:p-12 relative z-10">
                <form wire:submit.prevent="save">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        
                        {{-- Section 1: Personal Info --}}
                        <div class="space-y-6">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="p-2 bg-blue-500/20 rounded-lg">
                                    <svg class="w-6 h-6 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-white">البيانات الشخصية</h3>
                            </div>

                            {{-- First Name --}}
                            <div>
                                <label class="block text-sm font-medium text-blue-100/90 mb-2">الاسم الأول</label>
                                <input type="text" wire:model="form.first_name"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-white/40 focus:bg-white/10 focus:border-blue-400/50 focus:ring-2 focus:ring-blue-400/20 transition-all"
                                    placeholder="أدخل الاسم الأول">
                                @error('form.first_name') <span class="text-red-400 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Last Name --}}
                            <div>
                                <label class="block text-sm font-medium text-blue-100/90 mb-2">اسم العائلة</label>
                                <input type="text" wire:model="form.last_name"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-white/40 focus:bg-white/10 focus:border-blue-400/50 focus:ring-2 focus:ring-blue-400/20 transition-all"
                                    placeholder="أدخل اسم العائلة">
                                @error('form.last_name') <span class="text-red-400 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Email --}}
                            <div>
                                <label class="block text-sm font-medium text-blue-100/90 mb-2">البريد الإلكتروني</label>
                                <div class="relative">
                                    <input type="email" wire:model="form.email"
                                        class="w-full px-4 py-3 pr-11 bg-white/5 border border-white/10 rounded-xl text-white placeholder-white/40 focus:bg-white/10 focus:border-blue-400/50 focus:ring-2 focus:ring-blue-400/20 transition-all"
                                        placeholder="teacher@school.com">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                        </svg>
                                    </div>
                                </div>
                                @error('form.email') <span class="text-red-400 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Password --}}
                            <div>
                                <label class="block text-sm font-medium text-blue-100/90 mb-2">كلمة المرور</label>
                                <input type="password" wire:model="form.password"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-white/40 focus:bg-white/10 focus:border-blue-400/50 focus:ring-2 focus:ring-blue-400/20 transition-all"
                                    placeholder="••••••••">
                                @error('form.password') <span class="text-red-400 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Phone (Optional) --}}
                            <div>
                                <label class="block text-sm font-medium text-blue-100/90 mb-2">رقم الهاتف <span class="text-white/40 text-xs">(اختياري)</span></label>
                                <input type="text" wire:model="form.phone"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-white/40 focus:bg-white/10 focus:border-blue-400/50 focus:ring-2 focus:ring-blue-400/20 transition-all"
                                    placeholder="+966 xx xxx xxxx">
                                @error('form.phone') <span class="text-red-400 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Profile Photo --}}
                            <div>
                                <label class="block text-sm font-medium text-blue-100/90 mb-2">الصورة الشخصية <span class="text-white/40 text-xs">(اختياري)</span></label>
                                <input type="file" wire:model="form.photo"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all">
                                @error('form.photo') <span class="text-red-400 text-sm mt-1 block">{{ $message }}</span> @enderror
                                
                                @if ($form->photo)
                                    <div class="mt-2">
                                        <img src="{{ $form->photo->temporaryUrl() }}" class="h-20 w-20 rounded-full object-cover border-2 border-white/20">
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Section 2: Academic Info --}}
                        <div class="space-y-6">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="p-2 bg-purple-500/20 rounded-lg">
                                    <svg class="w-6 h-6 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-white">البيانات الأكاديمية</h3>
                            </div>

                            {{-- Specialization (Smart Select) --}}
                            <div>
                                <label class="block text-sm font-medium text-blue-100/90 mb-2">
                                    التخصص <span class="text-white/40 text-xs">(اختر أو اكتب جديد)</span>
                                </label>
                                <div class="relative">
                                    <input type="text" list="specializations" wire:model="form.specialization"
                                        class="w-full px-4 py-3 pr-11 bg-white/5 border border-white/10 rounded-xl text-white placeholder-white/40 focus:bg-white/10 focus:border-purple-400/50 focus:ring-2 focus:ring-purple-400/20 transition-all"
                                        placeholder="مثال: رياضيات، فيزياء، كيمياء...">
                                    <datalist id="specializations">
                                        @foreach($specializations as $spec)
                                            <option value="{{ $spec }}">
                                        @endforeach
                                    </datalist>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                </div>
                                @error('form.specialization') <span class="text-red-400 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Hire Date --}}
                            <div>
                                <label class="block text-sm font-medium text-blue-100/90 mb-2">تاريخ التعيين</label>
                                <input type="date" wire:model="form.hire_date"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-white/40 focus:bg-white/10 focus:border-purple-400/50 focus:ring-2 focus:ring-purple-400/20 transition-all">
                                @error('form.hire_date') <span class="text-red-400 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Max Weekly Classes --}}
                            <div>
                                <label class="block text-sm font-medium text-blue-100/90 mb-2">النصاب الأسبوعي (عدد الحصص)</label>
                                <input type="number" wire:model="form.max_weekly_classes"
                                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-white/40 focus:bg-white/10 focus:border-purple-400/50 focus:ring-2 focus:ring-purple-400/20 transition-all"
                                    placeholder="24">
                                @error('form.max_weekly_classes') <span class="text-red-400 text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Info Box --}}
                            <div class="mt-6 p-4 bg-blue-500/10 backdrop-blur-sm border border-blue-400/20 rounded-xl">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-blue-300 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div class="text-sm text-blue-200/90 leading-relaxed">
                                        <p class="font-medium mb-1">ملاحظة:</p>
                                        <p>سيتم إنشاء حساب المستخدم والملف الوظيفي تلقائياً عند حفظ البيانات. سيتمكن المعلم من تسجيل الدخول باستخدام البريد الإلكتروني وكلمة المرور المدخلة.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-10 pt-8 border-t border-white/10 flex flex-col sm:flex-row justify-end gap-3">
                        <button type="button" wire:click="saveAndCreateAnother"
                            class="px-6 py-3 bg-white/5 border border-white/10 rounded-xl text-white hover:bg-white/10 transition-all flex items-center justify-center gap-2 group">
                            <svg class="w-5 h-5 text-white/60 group-hover:text-white/80 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            حفظ وإضافة آخر
                        </button>
                        
                        <button type="submit"
                            class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl text-white font-bold shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:scale-105 transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            حفظ المعلم
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center mt-8 text-white/40 text-sm font-light">
            &copy; {{ date('Y') }} نظام إدارة المدرسة الذكي. جميع الحقوق محفوظة.
        </div>
    </div>

    {{-- Animations --}}
    <style>
        .animate-blob {
            animation: blob 7s infinite;
        }

        .animation-delay-2000 {
            animation-delay: 2s;
        }

        .animation-delay-4000 {
            animation-delay: 4s;
        }

        @keyframes blob {
            0% {
                transform: translate(0px, 0px) scale(1);
            }

            33% {
                transform: translate(30px, -50px) scale(1.1);
            }

            66% {
                transform: translate(-20px, 20px) scale(0.9);
            }

            100% {
                transform: translate(0px, 0px) scale(1);
            }
        }
    </style>
</div>

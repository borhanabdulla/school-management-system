<div class="py-6" x-data="{ 
    playSound(type) {
        // Audio feedback for speed entry
        const audio = new Audio(type === 'success' ? '/sounds/success.mp3' : '/sounds/error.mp3');
        audio.volume = 0.3;
        audio.play().catch(() => {});
    }
}" x-init="
    $watch('$wire.audioFeedback', value => {
        if (value) playSound(value);
    })
">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="min-h-screen" dir="rtl">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6 mb-8">
                <div>
                    <h1 class="text-3xl font-black bg-gradient-to-l from-red-600 via-orange-600 to-yellow-500 bg-clip-text text-transparent">
                        الرصد الأعمى
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        @if($this->session)
                            الدورة: <span class="font-bold text-red-600">{{ $this->session->name }}</span>
                        @else
                            <span class="text-red-500">لا توجد دورة نشطة</span>
                        @endif
                    </p>
                </div>

                <a href="{{ route('control.dashboard') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-xl transition-colors">
                    عودة للوحة
                </a>
            </div>

            @if($this->session)
                <!-- Subject Selection -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">اختر المادة</label>
                    <select wire:model.live="courseOfferingId"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-lg py-3">
                        <option value="">-- اختر المادة --</option>
                        @foreach($this->subjects as $subject)
                            <option value="{{ $subject->id }}">
                                {{ $subject->subject->name }} - {{ $subject->classSection->grade->name }} / {{ $subject->classSection->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($courseOfferingId)
                    <!-- Stats -->
                    @if($this->stats)
                        <div class="grid grid-cols-4 gap-4 mb-6">
                            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 text-center border border-gray-200 dark:border-gray-700">
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->stats['total'] }}</div>
                                <div class="text-xs text-gray-500">إجمالي الطلاب</div>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/30 rounded-xl p-4 text-center border border-green-200 dark:border-green-700">
                                <div class="text-2xl font-bold text-green-600">{{ $this->stats['entered'] }}</div>
                                <div class="text-xs text-green-600">تم الرصد</div>
                            </div>
                            <div class="bg-red-50 dark:bg-red-900/30 rounded-xl p-4 text-center border border-red-200 dark:border-red-700">
                                <div class="text-2xl font-bold text-red-600">{{ $this->stats['absent'] }}</div>
                                <div class="text-xs text-red-600">غائب</div>
                            </div>
                            <div class="bg-yellow-50 dark:bg-yellow-900/30 rounded-xl p-4 text-center border border-yellow-200 dark:border-yellow-700">
                                <div class="text-2xl font-bold text-yellow-600">{{ $this->stats['remaining'] }}</div>
                                <div class="text-xs text-yellow-600">متبقي</div>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-6">
                            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-green-500 to-green-600 transition-all duration-500"
                                     style="width: {{ $this->stats['percentage'] }}%"></div>
                            </div>
                            <div class="text-center text-sm text-gray-500 mt-1">{{ $this->stats['percentage'] }}% مكتمل</div>
                        </div>
                    @endif

                    <!-- Entry Form -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border-2 border-red-500 p-8 shadow-xl">
                        <div class="text-center mb-6">
                            <div class="text-6xl mb-2">🔐</div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">أدخل الرقم السري والدرجة</h2>
                            <p class="text-sm text-gray-500">اسم الطالب مخفي - الرصد بالرقم السري فقط</p>
                        </div>

                        <form wire:submit.prevent="submitGrade" class="space-y-6">
                            <!-- Secret Number -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">الرقم السري</label>
                                <input type="text" wire:model="secretNumber" wire:blur="validateSecret"
                                       placeholder="مثال: AB1234"
                                       class="w-full text-center text-3xl font-mono tracking-widest py-4 rounded-xl border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-red-500 focus:ring-red-500 uppercase"
                                       autofocus>
                            </div>

                            <!-- Score / Absent -->
                            <div class="grid grid-cols-3 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">الدرجة</label>
                                    <input type="number" wire:model="score" step="0.5" min="0" max="100"
                                           placeholder="0"
                                           class="w-full text-center text-4xl font-bold py-4 rounded-xl border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-red-500 focus:ring-red-500"
                                           {{ $isAbsent ? 'disabled' : '' }}>
                                </div>
                                <div class="flex flex-col justify-end">
                                    <label class="flex items-center justify-center gap-2 p-4 bg-red-50 dark:bg-red-900/30 rounded-xl cursor-pointer border-2 {{ $isAbsent ? 'border-red-500' : 'border-transparent' }}">
                                        <input type="checkbox" wire:model.live="isAbsent" class="rounded text-red-600 focus:ring-red-500">
                                        <span class="text-red-600 font-bold">غائب</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Submit -->
                            <button type="submit"
                                    class="w-full py-4 bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-700 hover:to-orange-700 text-white text-xl font-bold rounded-xl transition-all shadow-lg hover:shadow-xl">
                                حفظ (Enter)
                            </button>
                        </form>

                        <!-- Feedback -->
                        @if($lastEntry)
                            <div class="mt-6 p-4 bg-green-100 dark:bg-green-900/30 border border-green-300 rounded-xl text-center">
                                <span class="text-green-600 font-bold">✓ تم الحفظ:</span>
                                الرقم السري <span class="font-mono">{{ $lastEntry['secret'] }}</span> = {{ $lastEntry['score'] }}
                            </div>
                        @endif

                        @if($lastError)
                            <div class="mt-6 p-4 bg-red-100 dark:bg-red-900/30 border border-red-300 rounded-xl text-center">
                                <span class="text-red-600 font-bold">✗ خطأ:</span>
                                {{ $lastError }}
                            </div>
                        @endif
                    </div>

                    <!-- Keyboard Shortcuts -->
                    <div class="mt-6 text-center text-sm text-gray-500">
                        <strong>اختصارات:</strong> Enter = حفظ وانتقال | Tab = التنقل بين الحقول
                    </div>
                @endif
            @else
                <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-300 rounded-2xl p-8 text-center">
                    <div class="text-4xl mb-4">⚠️</div>
                    <h3 class="text-xl font-bold text-yellow-800 dark:text-yellow-300 mb-2">لا توجد دورة امتحانية نشطة</h3>
                    <p class="text-yellow-600 dark:text-yellow-400">يرجى تفعيل دورة امتحانية من لوحة الكنترول أولاً.</p>
                    <a href="{{ route('control.dashboard') }}" class="inline-block mt-4 px-6 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-xl">
                        الذهاب للوحة الكنترول
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

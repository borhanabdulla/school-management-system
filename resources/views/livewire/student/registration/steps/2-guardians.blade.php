<div class="space-y-8 animate-fade-in">
    <!-- Section Header -->
    <div class="flex items-center gap-4 pb-6 border-b border-gray-200 dark:border-gray-700/50">
        <div
            class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                </path>
            </svg>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">بيانات أولياء الأمور</h2>
            <p class="text-gray-500 dark:text-blue-200/70">يمكنك إضافة أكثر من ولي أمر وتحديد صلة القرابة</p>
        </div>
    </div>

    <!-- List of Added Guardians -->
    @if (count($addedGuardians) > 0)
        <div class="grid gap-4">
            @foreach ($addedGuardians as $index => $guardian)
                <div
                    class="flex items-center justify-between bg-gray-50 dark:bg-gray-800/50 p-6 rounded-2xl border border-gray-200 dark:border-gray-700/50 hover:border-emerald-500/30 transition-all hover:shadow-lg hover:shadow-emerald-500/10 group">
                    <div class="flex items-center gap-5">
                        <div
                            class="w-12 h-12 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold shadow-lg shadow-emerald-500/30 text-lg group-hover:scale-110 transition-transform">
                            {{ $index + 1 }}
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 dark:text-white text-lg">{{ $guardian['display_name'] }}</h4>
                            <div class="flex flex-wrap gap-2 mt-2">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-200 dark:bg-white/10 text-gray-700 dark:text-white border border-gray-300 dark:border-white/10">
                                    {{ $guardian['relationship'] === 'father' ? 'أب' : ($guardian['relationship'] === 'mother' ? 'أم' : 'آخر') }}
                                </span>
                                @if ($guardian['is_financial_sponsor'])
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/20 text-emerald-300 border border-emerald-500/20">
                                        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                            </path>
                                        </svg>
                                        مسؤول مالي
                                    </span>
                                @endif
                                @if ($guardian['is_emergency_contact'])
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-300 border border-red-500/20">
                                        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                            </path>
                                        </svg>
                                        طوارئ
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <button wire:click="removeGuardian({{ $index }})"
                        class="p-3 text-gray-400 dark:text-white/40 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                    </button>
                </div>
            @endforeach
        </div>
    @else
        <div
            class="text-center py-12 bg-gray-50 dark:bg-gray-800/30 rounded-3xl border-2 border-dashed border-gray-200 dark:border-gray-700 group hover:border-gray-300 dark:hover:border-white/20 transition-colors">
            <div
                class="w-20 h-20 bg-white dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400 dark:text-white/30 group-hover:scale-110 transition-transform duration-500">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                    </path>
                </svg>
            </div>
            <h3 class="text-xl font-medium text-gray-900 dark:text-white">قائمة أولياء الأمور فارغة</h3>
            <p class="text-gray-500 dark:text-white/50 mt-2">قم بإضافة ولي أمر واحد على الأقل للمتابعة</p>
        </div>
    @endif

    @error('guardians')
        <div
            class="p-4 bg-red-500/10 border border-red-500/20 text-red-300 rounded-xl flex items-center gap-3 animate-pulse">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-bold">{{ $message }}</span>
        </div>
    @enderror

    <!-- Add Guardian Form Section -->
    <div
        class="bg-white dark:bg-gray-800/40 p-8 rounded-3xl border border-gray-200 dark:border-gray-700/50 relative overflow-hidden shadow-sm dark:shadow-none">
        <div
            class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 rounded-full blur-2xl -mr-16 -mt-16 pointer-events-none">
        </div>

        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-8 flex items-center gap-3 relative z-10">
            <span class="w-1.5 h-8 bg-gradient-to-b from-blue-500 to-indigo-600 rounded-full"></span>
            إضافة ولي أمر جديد
        </h3>

        <!-- Search -->
        <div class="relative mb-8 z-20">
            <label class="block text-sm font-medium text-gray-700 dark:text-blue-200/80 mb-3">بحث عن ولي أمر موجود</label>
            <div class="relative group">
                <input wire:model.live="searchQuery" type="text"
                    placeholder="ابحث بالرقم القومي، الهاتف، أو الاسم..."
                    class="w-full pl-6 pr-12 py-4 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-black/20 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-white/30 focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all backdrop-blur-sm shadow-inner">
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                    <svg class="h-6 w-6 text-gray-400 dark:text-white/40 group-focus-within:text-blue-500 dark:group-focus-within:text-blue-400 transition-colors" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>

            @if (!empty($guardians) && count($guardians) > 0)
                <div
                    class="absolute z-30 w-full mt-2 bg-white dark:bg-gray-900/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200 dark:border-white/10 max-h-60 overflow-y-auto custom-scrollbar">
                    @foreach ($guardians as $guardian)
                        <button wire:click="selectGuardian({{ $guardian->id }})"
                            class="w-full text-right px-6 py-4 hover:bg-gray-50 dark:hover:bg-white/10 transition-all flex justify-between items-center group border-b border-gray-100 dark:border-white/5 last:border-0">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white text-lg group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                    {{ $guardian->first_name }} {{ $guardian->last_name }}</div>
                                <div class="text-sm text-gray-500 dark:text-white/50 flex gap-4 mt-1">
                                    <span class="flex items-center gap-1.5"><svg class="w-4 h-4" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                            </path>
                                        </svg> {{ $guardian->phone }}</span>
                                    <span class="flex items-center gap-1.5"><svg class="w-4 h-4" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2">
                                            </path>
                                        </svg> {{ $guardian->national_id }}</span>
                                </div>
                            </div>
                            <span
                                class="text-blue-400 bg-blue-500/10 px-4 py-1.5 rounded-full text-xs font-bold opacity-0 group-hover:opacity-100 transition-all transform translate-x-4 group-hover:translate-x-0">اختر</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($selectedGuardianId)
            <div
                class="bg-emerald-500/10 border border-emerald-500/20 rounded-2xl p-6 flex justify-between items-center mb-8 backdrop-blur-sm">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 shadow-lg shadow-emerald-500/10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <span class="block text-lg text-emerald-700 dark:text-white font-bold">تم تحديد ولي الأمر بنجاح</span>
                        <span class="text-sm text-emerald-600/70 dark:text-emerald-200/70">يمكنك الآن تحديد صلة القرابة وإضافته للقائمة</span>
                    </div>
                </div>
                <button wire:click="$set('selectedGuardianId', null)"
                    class="text-sm text-red-400 hover:text-red-300 font-bold hover:underline transition-colors">تغيير
                    الاختيار</button>
            </div>
        @else
            <div class="relative flex py-6 items-center">
                <div class="flex-grow border-t border-gray-200 dark:border-white/10"></div>
                <span class="flex-shrink-0 mx-6 text-gray-400 dark:text-white/30 text-sm font-medium uppercase tracking-wider">أو</span>
                <div class="flex-grow border-t border-gray-200 dark:border-white/10"></div>
            </div>
            <div class="text-center mb-8">
                <button wire:click="createNewGuardian"
                    class="inline-flex items-center px-6 py-3 border border-gray-200 dark:border-white/10 text-sm font-bold rounded-xl text-blue-600 dark:text-blue-300 bg-blue-50 dark:bg-blue-500/10 hover:bg-blue-100 dark:hover:bg-blue-500/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all hover:scale-105">
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                        </path>
                    </svg>
                    تسجيل ولي أمر جديد
                </button>
            </div>
        @endif

        <!-- New Guardian Form Fields -->
        @if ($isNewGuardian)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 animate-fade-in-up">
                <div class="relative group">
                    <input wire:model="form.guardian_first_name" type="text"
                        class="peer w-full px-5 py-4 bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-2xl text-gray-900 dark:text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all"
                        placeholder=" ">
                    <label
                        class="absolute right-5 -top-3 text-gray-500 dark:text-white/50 text-sm transition-all peer-focus:-top-3 peer-focus:text-xs peer-focus:bg-white dark:peer-focus:bg-gray-900 peer-focus:px-2 peer-focus:rounded-full peer-placeholder-shown:top-4 peer-placeholder-shown:text-base">الاسم
                        الأول</label>
                    @error('form.guardian_first_name')
                        <span class="text-red-400 text-xs mt-2 block">{{ $message }}</span>
                    @enderror
                </div>
                <div class="relative group">
                    <input wire:model="form.guardian_last_name" type="text"
                        class="peer w-full px-5 py-4 bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-2xl text-gray-900 dark:text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all"
                        placeholder=" ">
                    <label
                        class="absolute right-5 -top-3 text-gray-500 dark:text-white/50 text-sm transition-all peer-focus:-top-3 peer-focus:text-xs peer-focus:bg-white dark:peer-focus:bg-gray-900 peer-focus:px-2 peer-focus:rounded-full peer-placeholder-shown:top-4 peer-placeholder-shown:text-base">اسم
                        العائلة</label>
                    @error('form.guardian_last_name')
                        <span class="text-red-400 text-xs mt-2 block">{{ $message }}</span>
                    @enderror
                </div>
                <div class="relative group">
                    <input wire:model="form.guardian_phone" type="text"
                        class="peer w-full px-5 py-4 bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-2xl text-gray-900 dark:text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all"
                        placeholder=" ">
                    <label
                        class="absolute right-5 -top-3 text-gray-500 dark:text-white/50 text-sm transition-all peer-focus:-top-3 peer-focus:text-xs peer-focus:bg-white dark:peer-focus:bg-gray-900 peer-focus:px-2 peer-focus:rounded-full peer-placeholder-shown:top-4 peer-placeholder-shown:text-base">رقم
                        الهاتف</label>
                    @error('form.guardian_phone')
                        <span class="text-red-400 text-xs mt-2 block">{{ $message }}</span>
                    @enderror
                </div>
                <div class="relative group">
                    <input wire:model="form.guardian_national_id" type="text"
                        class="peer w-full px-5 py-4 bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-2xl text-gray-900 dark:text-white placeholder-transparent focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all"
                        placeholder=" ">
                    <label
                        class="absolute right-5 -top-3 text-gray-500 dark:text-white/50 text-sm transition-all peer-focus:-top-3 peer-focus:text-xs peer-focus:bg-white dark:peer-focus:bg-gray-900 peer-focus:px-2 peer-focus:rounded-full peer-placeholder-shown:top-4 peer-placeholder-shown:text-base">الرقم
                        القومي</label>
                    @error('form.guardian_national_id')
                        <span class="text-red-400 text-xs mt-2 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        @endif

        <!-- Relationship & Flags -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-6 border-t border-gray-200 dark:border-white/10">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-blue-200/80 mb-4">صلة القرابة <span
                        class="text-red-500 dark:text-red-400">*</span></label>
                <div class="grid grid-cols-3 gap-4">
                    <label class="cursor-pointer group">
                        <input type="radio" wire:model="relationship" value="father" class="peer sr-only">
                        <div
                            class="text-center py-3 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70 peer-checked:bg-blue-500 peer-checked:border-blue-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-blue-500/30 transition-all group-hover:border-gray-300 dark:group-hover:border-white/30">
                            أب</div>
                    </label>
                    <label class="cursor-pointer group">
                        <input type="radio" wire:model="relationship" value="mother" class="peer sr-only">
                        <div
                            class="text-center py-3 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70 peer-checked:bg-pink-500 peer-checked:border-pink-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-pink-500/30 transition-all group-hover:border-gray-300 dark:group-hover:border-white/30">
                            أم</div>
                    </label>
                    <label class="cursor-pointer group">
                        <input type="radio" wire:model="relationship" value="other" class="peer sr-only">
                        <div
                            class="text-center py-3 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70 peer-checked:bg-gray-600 peer-checked:border-gray-500 peer-checked:text-white transition-all group-hover:border-gray-300 dark:group-hover:border-white/30">
                            آخر</div>
                    </label>
                </div>
            </div>
            <div class="flex flex-col gap-4 justify-center">
                <label
                    class="flex items-center p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 cursor-pointer hover:bg-gray-100 dark:hover:bg-white/10 transition-all group">
                    <input wire:model="isFinancialSponsor" type="checkbox"
                        class="rounded-lg border-gray-300 dark:border-white/20 bg-white dark:bg-black/20 text-emerald-500 focus:ring-emerald-500/50 w-5 h-5">
                    <span
                        class="mr-3 text-sm font-medium text-gray-700 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-300 transition-colors">مسؤول
                        مالي (يدفع الرسوم)</span>
                </label>
                <label
                    class="flex items-center p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 cursor-pointer hover:bg-gray-100 dark:hover:bg-white/10 transition-all group">
                    <input wire:model="isEmergencyContact" type="checkbox"
                        class="rounded-lg border-gray-300 dark:border-white/20 bg-white dark:bg-black/20 text-red-500 focus:ring-red-500/50 w-5 h-5">
                    <span class="mr-3 text-sm font-medium text-gray-700 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-300 transition-colors">جهة
                        اتصال للطوارئ</span>
                </label>
            </div>
        </div>

        <div class="mt-10 flex justify-end">
            <button wire:click="addGuardianToList"
                class="px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-full hover:shadow-lg hover:shadow-blue-500/40 transition-all transform hover:-translate-y-1 font-bold flex items-center gap-3 text-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                إضافة ولي الأمر للقائمة
            </button>
        </div>
    </div>
</div>

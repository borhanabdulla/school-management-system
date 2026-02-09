<div class="space-y-8 animate-fade-in-up">
    <div class="text-center mb-8">
        <h3 class="text-2xl font-bold text-white mb-2">مراجعة البيانات والتأكيد</h3>
        <p class="text-white/60">يرجى مراجعة كافة البيانات قبل اعتماد التسجيل النهائي</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- بيانات الطالب -->
        <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6">
            <h4 class="text-lg font-bold text-white mb-4 flex items-center gap-2 border-b border-white/10 pb-2">
                <span class="text-blue-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></span>
                بيانات الطالب
            </h4>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-white/50">الاسم الكامل (عربي)</span>
                    <span class="text-white font-medium">{{ $form->first_name_ar }} {{ $form->family_name_ar }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-white/50">الاسم الكامل (English)</span>
                    <span class="text-white font-medium">{{ $form->first_name_en }} {{ $form->family_name_en }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-white/50">رقم الهوية / الإقامة</span>
                    <span class="text-white font-medium">{{ $form->national_id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-white/50">تاريخ الميلاد</span>
                    <span class="text-white font-medium">{{ $form->date_of_birth }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-white/50">الجنسية</span>
                    <span class="text-white font-medium">
                        @php $country = \App\Domains\Shared\Models\Country::find($form->nationality_id); @endphp
                        {{ $country ? $country->name_ar : '-' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- البيانات الأكاديمية -->
        <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6">
            <h4 class="text-lg font-bold text-white mb-4 flex items-center gap-2 border-b border-white/10 pb-2">
                <span class="text-indigo-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg></span>
                البيانات الأكاديمية
            </h4>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-white/50">نوع التسجيل</span>
                    <span
                        class="text-white font-medium">{{ $isTransferStudent ? 'منقول من مدرسة أخرى' : 'طالب مستجد' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-white/50">الصف الدراسي المطلوب</span>
                    <span class="text-white font-medium">
                        @php $grade = \App\Domains\Academic\Grade\Models\Grade::find($form->grade_id); @endphp
                        {{ $grade ? $grade->name : '-' }}
                    </span>
                </div>
                @if ($form->class_section_id)
                    <div class="flex justify-between">
                        <span class="text-white/50">الفصل المختار</span>
                        <span class="text-white font-medium">
                            @php $section = \App\Domains\Academic\ClassSection\Models\ClassSection::find($form->class_section_id); @endphp
                            {{ $section ? $section->name : '-' }}
                        </span>
                    </div>
                @endif

                @if ($isTransferStudent)
                    <div class="mt-4 pt-4 border-t border-white/10">
                        <div class="text-xs text-white/40 mb-2">بيانات النقل</div>
                        <div class="flex justify-between mb-1">
                            <span class="text-white/50">المدرسة السابقة</span>
                            <span class="text-white font-medium">{{ $form->school_name }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- أولياء الأمور -->
        <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6">
            <h4 class="text-lg font-bold text-white mb-4 flex items-center gap-2 border-b border-white/10 pb-2">
                <span class="text-purple-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></span>
                أولياء الأمور ({{ count($addedGuardians) }})
            </h4>
            <div class="space-y-3">
                @foreach ($addedGuardians as $guardian)
                    <div class="bg-white/5 rounded-lg p-3 text-sm">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-white font-bold">{{ $guardian['display_name'] }}</span>
                            <span class="text-xs px-2 py-0.5 rounded bg-white/10 text-white/70">
                                {{ $guardian['relationship'] == 'father' ? 'أب' : ($guardian['relationship'] == 'mother' ? 'أم' : 'آخر') }}
                            </span>
                        </div>
                        <div class="text-white/50 text-xs flex gap-3">
                            <span>{{ $guardian['data']['phone'] ?? '-' }}</span>
                            @if ($guardian['is_financial_sponsor'])
                                <span class="text-emerald-400">الكفيل المالي</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- الملخص المالي -->
        <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-6">
            <h4 class="text-lg font-bold text-white mb-4 flex items-center gap-2 border-b border-white/10 pb-2">
                <span class="text-emerald-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                الملخص المالي
            </h4>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-white/50">عدد بنود الرسوم</span>
                    <span class="text-white font-medium">{{ count($selected_fee_types) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-white/50">إجمالي الخصومات</span>
                    <span class="text-white font-medium">{{ number_format($discount_amount, 2) }} ر.س</span>
                </div>
                <div class="flex justify-between text-lg font-bold pt-2 border-t border-white/10">
                    <span class="text-white">الإجمالي المستحق</span>
                    <span class="text-emerald-400">{{ number_format($final_total, 2) }} ر.س</span>
                </div>
                <div
                    class="mt-4 p-3 rounded-lg {{ $create_invoice ? 'bg-emerald-500/10 border border-emerald-500/20' : 'bg-gray-500/10 border border-gray-500/20' }}">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full {{ $create_invoice ? 'bg-emerald-500' : 'bg-gray-500' }}">
                        </div>
                        <span class="text-white/80 text-xs">
                            {{ $create_invoice ? 'سيتم إصدار فاتورة فورية' : 'لن يتم إصدار فاتورة الآن' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

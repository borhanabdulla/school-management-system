@props(['student'])

<div class="space-y-6">
    <!-- Personal Information -->
    <div class="bg-surface rounded-2xl shadow-lg p-6 border border-border">
        <h3 class="text-lg font-bold text-foreground mb-6 flex items-center gap-2">
            <span class="w-1 h-6 bg-blue-500 rounded-full"></span>
            البيانات الشخصية
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            <div class="p-4 bg-background/50 rounded-xl border border-border">
                <dt class="text-sm font-medium text-muted-foreground mb-1">الاسم بالكامل (عربي)</dt>
                <dd class="text-lg font-semibold text-foreground">
                    {{ $student->full_name_ar }}
                </dd>
            </div>
            <div class="p-4 bg-background/50 rounded-xl border border-border">
                <dt class="text-sm font-medium text-muted-foreground mb-1">الاسم بالكامل (إنجليزي)</dt>
                <dd class="text-lg font-semibold text-foreground">
                    {{ $student->full_name_en }}
                </dd>
            </div>
            <div class="p-4 bg-background/50 rounded-xl border border-border">
                <dt class="text-sm font-medium text-muted-foreground mb-1">تاريخ الميلاد</dt>
                <dd class="text-lg font-semibold text-foreground">
                    {{ $student->date_of_birth }}
                </dd>
            </div>
            <div class="p-4 bg-background/50 rounded-xl border border-border">
                <dt class="text-sm font-medium text-muted-foreground mb-1">الرقم القومي</dt>
                <dd class="text-lg font-semibold text-foreground">
                    {{ $student->national_id }}
                </dd>
            </div>
            <div class="p-4 bg-background/50 rounded-xl border border-border">
                <dt class="text-sm font-medium text-muted-foreground mb-1">فصيلة الدم</dt>
                <dd class="text-lg font-semibold text-foreground">
                    {{ $student->blood_type ?? '-' }}
                </dd>
            </div>
            <div class="p-4 bg-background/50 rounded-xl border border-border">
                <dt class="text-sm font-medium text-muted-foreground mb-1">البريد الإلكتروني</dt>
                <dd class="text-lg font-semibold text-foreground">
                    {{ $student->email ?? 'لا يوجد' }}
                </dd>
            </div>
        </div>

        @if ($student->address || $student->phone)
            <div class="mt-8 pt-6 border-t border-border">
                <h4 class="text-md font-semibold text-foreground mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-green-500 rounded-full"></span>
                    معلومات الاتصال
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if ($student->address)
                        <div class="p-4 bg-background/50 rounded-xl border border-border">
                            <dt class="text-sm font-medium text-muted-foreground mb-1">العنوان</dt>
                            <dd class="text-lg font-semibold text-foreground">
                                {{ $student->address }}
                            </dd>
                        </div>
                    @endif

                    @if ($student->phone)
                        <div class="p-4 bg-background/50 rounded-xl border border-border">
                            <dt class="text-sm font-medium text-muted-foreground mb-1">رقم الهاتف</dt>
                            <dd class="text-lg font-semibold text-foreground">
                                <a href="tel:{{ $student->phone }}"
                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
                                    {{ $student->phone }}
                                </a>
                            </dd>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

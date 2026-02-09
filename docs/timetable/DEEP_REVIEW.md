# 📋 مراجعة شاملة لجدول الدرس (Timetable) - تحليل عميق

## 🎯 السيناريوهات المُحللة

### السيناريو 1: مدير المدرسة ينشئ جدولاً جديداً
### السيناريو 2: معلم يحاول رصد الحضور
### السيناريو 3: حذف حصة موجودة
### السيناريو 4: تحديث قالب الجدول
### السيناريو 5: إدارة البدائل

---

## 🚨 المشاكل الحرجة (Critical Issues)

### المشكلة 1: حذف الحصص بدون التحقق من البيانات المرتبطة

**الموقع:** [`app/Domains/Academic/Timetable/Actions/UpdateTimetableTemplateAction.php:40`](app/Domains/Academic/Timetable/Actions/UpdateTimetableTemplateAction.php:40)

```php
$template->timeSlots()->delete(); // ❌ يحذف بدون التحقق
```

**المخاطر:**
1. إذا كانت الحصص مستخدمة في `timetables`، ستصبح البيانات يتيمة
2. سجلات `attendances` ستفقد السياق
3. سجلات `substitutions` ستفقد المرجع

**سيناريو الكسر:**
```
مستخدم لديه:
├── قالب جدول بـ 10 حصص
├── 50 حصة timetable مرتبطة
├── 200 سجل حضور للطلاب
└── 5 بدائل معلمة

عند تحديث القالب:
└── ❌ جميع البيانات المرتبطة ستصبح يتيمة!
```

**خطة الإصلاح:**

```php
// 1. التحقق قبل الحذف
private function ensureNoLinkedTimetableEntries(TimeSlot $slot): void
{
    $timetableCount = Timetable::where('time_slot_id', $slot->id)->count();
    if ($timetableCount > 0) {
        throw CannotDeleteException::make(
            "لا يمكن حذف الحصة لأنها مستخدمة في {$timetableCount} حصة جدول"
        );
    }
}

// 2. أو حذف متتابع مع التحقق
public function execute(TimetableTemplate $template, TimetableTemplateData $data): TimetableTemplate
{
    // التحقق من وجود حصص timetable مرتبطة
    $linkedTimetableCount = Timetable::whereHas('timeSlot', function ($q) use ($template) {
        $q->where('template_id', $template->id);
    })->count();

    if ($linkedTimetableCount > 0) {
        throw InvalidOperationException::make(
            "لا يمكن تحديث القالب لوجود {$linkedTimetableCount} حصص جدول مرتبطة. " .
            "يرجى حذف الحصص المرتبطة أولاً أو إنشاء قالب جديد."
        );
    }

    // ... continue with update
}
```

---

### المشكلة 2: حذف timetable session بدون التحقق من attendance

**الموقع:** [`app/Livewire/Academic/TimetableBuilder.php:475-478`](app/Livewire/Academic/TimetableBuilder.php:475)

```php
Timetable::where('class_section_id', $this->selectedSectionId)
    ->where('time_slot_id', $slotId)
    ->where('term_id', $this->selectedTermId)
    ->delete(); // ❌ بدون التحقق من attendance
```

**المخاطر:**
1. سجلات الحضور ستفقد مرجع الـ timetable
2. لن يمكن تتبع المادة والمعلم للحضور المسجل
3. تقارير الحضور ستصبح غير دقيقة

**سيناريو الكسر:**
```
معلم سجل حضور لـ 30 طالب في حصة
مدير يحذف الحصة من الجدول
└── ❌ الآن attendance records لا تشير لأي مادة أو مععلم!
```

**خطة الإصلاح:**

```php
public function deleteSession($slotId)
{
    if ($this->isReadOnly) {
        $this->dispatch('error', message: __('attendance.timetable_read_only'));
        return;
    }

    // ✅ التحقق من وجود حضور
    $timetable = Timetable::where('class_section_id', $this->selectedSectionId)
        ->where('time_slot_id', $slotId)
        ->where('term_id', $this->selectedTermId)
        ->first();

    if (!$timetable) {
        $this->dispatch('error', message: 'الحصة غير موجودة');
        return;
    }

    // ✅ التحقق من وجود سجلات حضور
    $attendanceCount = Attendance::where('timetable_id', $timetable->id)->count();
    if ($attendanceCount > 0) {
        $this->dispatch('error', message: 
            "لا يمكن حذف هذه الحصة لوجود {$attendanceCount} سجل حضور. " .
            "يرجى حذف سجلات الحضور أولاً أو أرشفتها.");
        return;
    }

    // ✅ التحقق من وجود بدائل
    $substitutionCount = Substitution::where('timetable_id', $timetable->id)->count();
    if ($substitutionCount > 0) {
        $this->dispatch('error', message: 
            "لا يمكن حذف هذه الحصة لوجود {$substitutionCount} بديل. " .
            "يرجى حذف البدائل أولاً.");
        return;
    }

    $timetable->delete();
    $this->dispatch('notify', message: 'تم حذف الحصة', type: 'success');
}
```

---

### المشكلة 3: عدم اتساق Foreign Keys في قاعدة البيانات

**جدول [`attendances`](database/migrations/2025_12_05_203721_setup_flexible_attendance_system.php:44):**
```php
$table->foreignId('time_slot_id')->nullable()->constrained()->nullOnDelete();
```

**جدول [`substitutions`](database/migrations/2025_12_19_175647_create_substitutions_table.php:17):**
```php
$table->foreignId('timetable_id')->constrained()->onDelete('cascade');
```

**المشكلة:**
- `attendances` يستخدم `time_slot_id`
- `substitutions` يستخدم `timetable_id`
- لا يوجد `timetable_id` في جدول `attendances`

**خطة الإصلاح - إضافة migration:**

```php
// إضافة timetable_id لجدول attendances
Schema::table('attendances', function (Blueprint $table) {
    $table->foreignId('timetable_id')
        ->nullable()
        ->after('time_slot_id')
        ->constrained()
        ->nullOnDelete();
});

// تحديث بيانات الـ attendance الموجودة
DB::statement("
    UPDATE attendances a
    SET timetable_id = (
        SELECT t.id
        FROM timetables t
        INNER JOIN time_slots ts ON ts.id = a.time_slot_id
        WHERE t.time_slot_id = ts.id
        AND t.class_section_id = a.class_section_id
        LIMIT 1
    )
    WHERE a.timetable_id IS NULL
    AND a.time_slot_id IS NOT NULL
");
```

---

### المشكلة 4: تحديث course_offering بدون التحقق من البيانات المرتبطة

**الموقع:** [`app/Domains/Academic/Timetable/Actions/AssignSessionAction.php:70-79`](app/Domains/Academic/Timetable/Actions/AssignSessionAction.php:70)

```php
return Timetable::updateOrCreate(
    [
        'class_section_id' => $sectionId,
        'time_slot_id' => $slotId,
        'term_id' => $termId,
    ],
    [
        'course_offering_id' => $courseOffering->id
    ]
); // ❌ قد يغير الـ course_offering لحصة موجودة
```

**المشكلة:**
- إذا كانت الحصة موجودة مسبقاً وتم تسجيل حضور لها
- تغيير `course_offering_id` قد يؤثر على تقارير الحضور
- المعلم الأصلي سيختفي من السجلات

**خطة الإصلاح:**

```php
// التحقق قبل التحديث
$timetable = Timetable::where('class_section_id', $sectionId)
    ->where('time_slot_id', $slotId)
    ->where('term_id', $termId)
    ->first();

if ($timetable) {
    // ✅ التحقق من وجود حضور مرتبط
    $attendanceCount = Attendance::where('timetable_id', $timetable->id)->count();
    if ($attendanceCount > 0 && $timetable->course_offering_id != $courseOffering->id) {
        throw InvalidOperationException::make(
            "لا يمكن تغيير المقرر لوجود {$attendanceCount} سجل حضور. " .
            "يرجى حذف سجلات الحضور أولاً أو إنشاء حصة جديدة."
        );
    }
}

return Timetable::updateOrCreate([...]);
```

---

## 🟡 المشاكل المتوسطة (Medium Issues)

### المشكلة 5: Cast datetime غير صحيح

**الموقع:** [`app/Domains/Academic/Timetable/Models/TimeSlot.php:41-47`](app/Domains/Academic/Timetable/Models/TimeSlot.php:41)

```php
protected $casts = [
    'start_time' => 'datetime:H:i', // ❌ غير صحيح
    'end_time' => 'datetime:H:i',
];
```

**الإصلاح:**
```php
protected $casts = [
    'start_time' => 'immutable_date:H:i',
    'end_time' => 'immutable_date:H:i',
];
```

---

### المشكلة 6: deleteSession يستخدم Query Builder بدلاً من Model

**الموقع:** [`app/Livewire/Academic/TimetableBuilder.php:475`](app/Livewire/Academic/TimetableBuilder.php:475)

```php
Timetable::where(...)->delete(); // ❌ لا يُطلق events
```

**الإصلاح:**
```php
$timetable = Timetable::where(...)->first();
if ($timetable) {
    $timetable->delete(); // يُطلق events و cascading
}
```

---

### المشكلة 7: العلاقة timetableEntries لا تفلتر بـ term

**الموقع:** [`app/Domains/Academic/Timetable/Models/TimeSlot.php:57`](app/Domains/Academic/Timetable/Models/TimeSlot.php:57)

```php
public function timetableEntries(): HasMany
{
    return $this->hasMany(Timetable::class); // ❌ يرجع كل التروم
}
```

**الإصلاح:**
```php
public function timetableEntries(): HasMany
{
    return $this->hasMany(Timetable::class);
}

public function timetableEntriesForTerm(int $termId): HasMany
{
    return $this->hasMany(Timetable::class)->where('term_id', $termId);
}
```

---

## 🟢 نقاط إيجابية (Good Practices)

1. ✅ `TimetableService::checkTeacherConflict` يمرر `termId` بشكل صحيح
2. ✅ `TimetableBuilder::isReadOnly` يفحص حالة السنة والترم
3. ✅ `RecordStudentAttendanceAction` يتحقق من العطلات
4. ✅ `HandlesSafeDelete` Trait موجود ومُستخدم بشكل صحيح للحذف

---

## 📋 خطة الإصلاح المرحلية

### المرحلة 1: إصلاح مشاكل البيانات (Critical)
- [ ] إصلاح `UpdateTimetableTemplateAction` للتحقق من linked timetables
- [ ] إصلاح `TimetableBuilder::deleteSession` للتحقق من attendance
- [ ] إضافة migration لإضافة `timetable_id` لجدول attendances
- [ ] تحديث `AssignSessionAction` للتحقق من attendance قبل تغيير course_offering

### المرحلة 2: إصلاح مشاكل الـ Casts
- [ ] إصلاح `TimeSlot` casts لـ immutable_date

### المرحلة 3: تحسين العلاقات
- [ ] إضافة علاقة `timetableEntriesForTerm` لـ TimeSlot
- [ ] تحديث `DeleteTimetableTemplateAction` لاستخدام protected relations

### المرحلة 4: تحسين الـ Performance
- [ ] إضافة فهارس للبحث السريع
- [ ] تحسين eager loading

---

## 🧪 سيناريوهات الاختبار

### Test Case 1: حذف حصة لها حضور
```
1. إنشاء حصة timetable
2. تسجيل حضور لـ 5 طلاب
3. محاولة حذف الحصة
4. النتيجة: خطأ مع رسالة توضح عدد سجلات الحضور
```

### Test Case 2: تحديث قالب له حصص timetable
```
1. إنشاء قالب بـ 5 حصص
2. إنشاء timetable entries مرتبطة
3. محاولة تحديث القالب
4. النتيجة: خطأ مع رسالة توضح عدد الحصص المرتبطة
```

### Test Case 3: تغيير course_offering لحصة لها حضور
```
1. إنشاء حصة timetable بـ course_offering A
2. تسجيل حضور للطلاب
3. محاولة تعيين course_offering B لنفس الحصة
4. النتيجة: خطأ مع رسالة توضح عدم إمكانية التغيير
```

### Test Case 4: حذف timetable يحذف substitutions
```
1. إنشاء حصة timetable
2. إنشاء substitution للحصة
3. حذف الحصة
4. النتيجة: الـ substitution يُحذف تلقائياً (cascade)
```

---

**تاريخ المراجعة:** 2026-02-04
**المُراجع:** Kilo Code AI - وضع المحلل الصارم

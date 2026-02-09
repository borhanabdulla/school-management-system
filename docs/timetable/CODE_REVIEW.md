# مراجعه جزئية لجدول الدرس (Timetable)

## الملخص التنفيذي

تم مراجعة الملفات المتعلقة بجدول الدرس في المشروع. الهيكلة العامة جيدة مع وجود بعض المشاكل المحتملة التي قد تؤدي إلى كسر الكود.

---

## المشاكل المكتشفة

### 1. مشكلة الـ Cast لـ TimeSlot (منطقية - 🔴 متوسطة)

**الموقع:** [`app/Domains/Academic/Timetable/Models/TimeSlot.php:41-48`](app/Domains/Academic/Timetable/Models/TimeSlot.php:41)

```php
protected $casts = [
    'start_time' => 'datetime:H:i',
    'end_time' => 'datetime:H:i',
];
```

**المشكلة:** استخدام `datetime:H:i` غير صحيح في Laravel. التنسيق الصحيح هو `immutable_date:H:i` أو `datetime:H:i` يعمل فقط في Laravel 11+.

**الأثر:** قد يسبب مشاكل في حساب المدة والفترات الزمنية.

**الاقتراح:**
```php
protected $casts = [
    'start_time' => 'immutable_date:H:i',
    'end_time' => 'immutable_date:H:i',
];
```

---

### 2. عدم وجود علاقة مباشرة بين Substitution و Term (معمارية - 🟡 منخفضة)

**الموقع:** [`app/Domains/HR/Substitution/Models/Substitution.php`](app/Domains/HR/Substitution/Models/Substitution.php)

**المشكلة:** نموذج `Substitution` لا يحتوي على `term_id` مباشر، بل يعتمد على العلاقة مع `Timetable` للحصول على الترم.

```php
public function timetable(): BelongsTo
{
    return $this->belongsTo(Timetable::class);
}
```

**الأثر:**
- استعلامات الفلترة حسب الترم تتطلب JOIN إضافي
- صعوبة في تتبع البدائل حسب الترم مباشرة
- قد يسبب N+1 queries عند جلب البدائل

**الاقتراح:** إضافة `term_id` مباشر إلى جدول `substitutions` مع foreign key.

---

### 3. TimeSlot -> timetableEntries علاقة غير محددة بـ Term (منطقية - 🟡 منخفضة)

**الموقع:** [`app/Domains/Academic/Timetable/Models/TimeSlot.php:57-60`](app/Domains/Academic/Timetable/Models/TimeSlot.php:57)

```php
public function timetableEntries(): HasMany
{
    return $this->hasMany(Timetable::class);
}
```

**المشكلة:** هذه العلاقة لا تأخذ في الاعتبار الفلترة حسب الترم. عند استدعاء `$timeSlot->timetableEntries` قد تُرجع حصص من تروم مختلفة.

**الأثر:** قد يسبب عرض بيانات خاطئة عند استخدام العلاقة مباشرة.

**الاقتراح:**
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

### 4. التحقق من التعارض في TimetableService (معمارية جيدة ✓)

تم تنفيذ التحقق من التعارض بشكل صحيح في [`TimetableService.php`](app/Domains/Academic/Timetable/Services/TimetableService.php) مع تمرير `termId`:

```php
public function checkTeacherConflict(int $teacherId, int $slotId, int $academicYearId, int $termId, ?int $excludeSectionId = null): ?array
{
    return Timetable::with([...])
        ->where('time_slot_id', $slotId)
        ->where('term_id', $termId) // ✅ تم إضافته بشكل صحيح
        ...
}
```

---

### 5. مشكلة محتملة في TimetableBuilder (🔴 متوسطة)

**الموقع:** [`app/Livewire/Academic/TimetableBuilder.php:219-236`](app/Livewire/Academic/TimetableBuilder.php:219)

```php
#[Computed]
public function timetableMatrix()
{
    $termId = $this->selectedTermId ?? app(AcademicContextService::class)->activeTerm()?->id;

    if (!$termId)
        return collect();

    return Timetable::with([...])
        ->where('class_section_id', $this->selectedSectionId)
        ->where('term_id', $termId)
        ->get()
        ->keyBy('time_slot_id');
}
```

**المشكلة:** إذا كان `selectedTermId` null، يتم استخدام `activeTerm()`، لكن إذا اختار المستخدم سنة مختلفة، قد لا يكون هناك ترم نشط.

**الأثر:** قد يظهر خطأ أو返回一个 空 مجموعة عند اختيار سنة غير نشطة.

---

### 6. الصلاحيات في AttendanceTaker (🟢 جيدة)

تم تنفيذ فحص الأمان بشكل صحيح في [`AttendanceTaker.php:36-40`](app/Livewire/Teacher/AttendanceTaker.php:36):

```php
$activeTermId = school()->activeTerm()?->id;
if ($this->timetable->term_id && $activeTermId && $this->timetable->term_id != $activeTermId) {
    abort(403, __('attendance.cannot_take_attendance_non_active_term'));
}
```

---

### 7. Migration إضافة term_id (🟢 جيدة)

تم تنفيذ migration بشكل صحيح مع guards في [`2026_02_03_225114_add_term_id_to_timetables_with_strict_guard.php`](database/migrations/2026_02_03_225114_add_term_id_to_timetables_with_strict_guard.php):
- ✅ Backfill البيانات من course_offerings
- ✅ التحقق من عدم وجود orphaned records
- ✅ Enforcement Not Null
- ✅ Unique constraint محدث

---

## قائمة التحقق للاختبار

- [ ] اختبار حساب مدة الحصة (`durationMinutes`)
- [ ] اختبار عرض جدول الحصص عند اختيار سنة غير نشطة
- [ ] اختبار إنشاء بديل مع استعلامات محسنة
- [ ] اختبار العلاقة timetableEntries عند وجود حصص في تروم متعددة
- [ ] اختبار تعيين حصة مع course_offering_id null

---

## الأولويات

| الأولوية | المشكلة | الملف |
|---------|--------|-------|
| 🔴 متوسطة | Cast datetime:H:i | TimeSlot.php:41 |
| 🔴 متوسطة | selectedTermId null handling | TimetableBuilder.php:219 |
| 🟡 منخفضة | إضافة term_id للـ Substitution | Substitution.php |
| 🟡 منخفضة | timetableEntries relationship | TimeSlot.php:57 |

---

**تاريخ المراجعة:** 2026-02-04
**المُراجع:** Kilo Code AI

# الخطة النهائية المعتمدة: Timetable + Calendar + Holidays (منقّحة)

> **قاعدة ذهبية:** "كل كتابة/حذف تمر عبر Actions. كل قرار زمني مرتبط بـ yearId/termId صريح. التقويم range-aware. والـ DB constraints تصبح فعّالة لأن مسارات الكتابة تدعمها."

## هدف الخطة

1. منع أي "تسريب زمني" (Active Year/Term غلط) في الجدول/الحضور/التقويم
2. منع أي Bypass للـ Traits/Events/Audit عبر Query Builder deletes
3. جعل قيود قاعدة البيانات فعّالة واقعياً (خصوصاً attendance unique)
4. ضمان "مصدر الحقيقة الواحد" لأيام الإجازات/الويكند/أيام الدوام

---

## ترتيب PRs (مُصحّح)

| PR | العنوان | الاعتماد |
|----|---------|----------|
| PR-0 | إصلاح Migration DB import | - |
| PR-1 | Timetable Protected Relations | PR-0 |
| PR-2 | Centralized Deletion Action | PR-1 |
| PR-3 | Attendance timetable_id | PR-2 |
| PR-4 | Calendar Year-Aware | PR-0 |
| PR-5 | Attendance Holiday Check Year | PR-3 + PR-4 |
| PR-6 | WeeklyTimetable weekend_days | PR-4 |
| PR-7 | CourseOffering Policy | PR-3 |

---

## مبادئ الهندسة (مستخرجة من هذه الخطة)

| # | المبدأ | التطبيق في هذه الخطة |
|---|--------|---------------------|
| 1 | **Single Source of Truth** | التقويم/الويكند يأتي من `academic_years`، ليس من config/hardcode |
| 2 | **Domain Invariants in One Place** | Delete actions تحمي العلاقات المحمية في طبقة واحدة |
| 3 | **No Query Builder Bypass** | كل حذف يمر عبر Model delete → Events/Traits |
| 4 | **Context-Driven Year** | yearId من سياق الشاشة (term/section) وليس activeYear دائماً |
| 5 | **Explicit Time Identity** | كل سجل حضور يعرف timetable_id الخاص به |
| 6 | **Query > Load All** | فحص holiday يستخدم exists() بدلاً من تحميل كل شيء |
| 7 | **Guard Early, Fail Fast** | منع الحضور في عطلة قبل写入 DB |
| 8 | **DB Constraints + Write Paths** | unique constraints تشتغل لأن مسارات الكتابة تدعمها |

---

## PR-0: Fix Build Breaker (Migration term_id)

**هدف:** منع فشل migrations

**المبدأ:** لا تجعل Migration تعتمد على كود غير موجود.

**تعديل:** أضف `use Illuminate\Support\Facades\DB;`

**معيار قبول:**
- `php artisan migrate` يعمل على بيئة نظيفة بدون error

---

## PR-1: Timetable Protected Relations Realization

**هدف:** `HandlesSafeDelete` يشتغل فعلياً + العلاقات موجودة

**المبدأ:** العلاقات المحمية تُعرّف في Model وتُنفّذ عند الحذف.

**الخطر لو تركته:** حذف timetable يترك orphan attendances.

### 1) تحديث Timetable Model

**ملف:** `app/Domains/Academic/Timetable/Models/Timetable.php`

```php
/**
 * الحضور المرتبط بهذه الحصة
 */
public function attendances(): HasMany
{
    return $this->hasMany(
        \App\Domains\Academic\Attendance\Models\Attendance::class,
        'timetable_id'
    );
}

/**
 * البدائل المرتبطة بهذه الحصة
 */
public function substitutions(): HasMany
{
    // ⚠️ استخدم المسار الفعلي الموجود في المشروع
    // جرب: `grep -r "class Substitution" app/` أو ابحث عن namespace الفعلي
    return $this->hasMany(
        \App\Domains\HR\Models\Substitution::class, // تحقق من المسار الفعلي
        'timetable_id'
    );
}
```

### 2) تحديث Attendance Model

**ملف:** `app/Domains/Academic/Attendance/Models/Attendance.php`

```php
protected $fillable = [
    'student_id',
    'date',
    'time_slot_id',
    'timetable_id', // ✅ NEW
    'status',
    'notes',
    // ... other fields
];

public function timetable(): BelongsTo
{
    return $this->belongsTo(\App\Domains\Academic\Timetable\Models\Timetable::class);
}
```

**معيار قبول:**
- محاولة حذف Timetable لها attendances → يتمنع
- العلاقات معرفة بشكل صحيح مع foreign key

---

## PR-2: Centralized Timetable Entry Deletion

**هدف:** أي حذف Timetable Entry يمر عبر Model delete داخل Action

**المبدأ:** Domain invariants تُنفّذ في مكان واحد.

**الخطر لو تركته:** query delete يتجاوز Events/Traits → orphan data + تاريخ مزيف.

**المرجع في الكود:** `TimetableBuilder` حذف مباشر بـ Query Builder.

### 0) مصدر timetableId

> **ملاحظة:** `timetableId` يُجلب عبر جلب الموديل بالمفاتيح المركبة `(termId+sectionId+slotId)` داخل UI/Action، ثم يُمرر للـ Action. هذا يضمن أن الـ ID موجود فعليًا قبل الحذف.

### 1) إنشاء Action جديد

**ملف:** `app/Domains/Academic/Timetable/Actions/DeleteTimetableEntryAction.php`

```php
class DeleteTimetableEntryAction
{
    /**
     * حذف entrée جدول مع حماية من الـ orphan records
     * 
     * @param int $timetableId معرف الجدول المراد حذفه
     * @throws CannotDeleteTimetableWithAttendanceException عند وجود حضور مرتبط
     */
    public function execute(int $timetableId): void
    {
        DB::transaction(function () use ($timetableId) {
            $timetable = Timetable::find($timetableId);

            if (!$timetable) {
                return;
            }

            // Guard: Check protected relations (PR-1)
            // ملاحظة: HandlesSafeDelete سيعمل تلقائيًا بعد حذف الموديل
            // الفحص اليدوي هنا لإخراج رسالة Exception Domain مخصصة
            $attendanceCount = $timetable->attendances()->count();
            if ($attendanceCount > 0) {
                throw CannotDeleteTimetableWithAttendanceException::forTimetable(
                    $timetable,
                    $attendanceCount
                );
            }

            // Delete via model (triggers HandlesSafeDelete + Observers)
            $timetable->delete();
        });
    }
}
```

### 2) تعديل Livewire

**ملف:** `app/Livewire/Academic/TimetableBuilder.php`

```php
// قبل (❌)
Timetable::where(...)->delete();

// بعد (✅)
$timetable = Timetable::where(...)->first();
if ($timetable) {
    app(DeleteTimetableEntryAction::class)->execute($timetable->id);
}
```

### 3) تعديل DeleteTimetableTemplateAction

**ملف:** `app/Domains/Academic/Timetable/Actions/DeleteTimetableTemplateAction.php`

```php
// قبل (❌)
$deletedSessions = Timetable::whereIn('time_slot_id', $slotIds)->delete();

// بعد (✅)
$timetablesToDelete = Timetable::whereIn('time_slot_id', $slotIds)
    ->where('term_id', $termId) // ✅ مع term scope
    ->get();

foreach ($timetablesToDelete as $timetable) {
    app(DeleteTimetableEntryAction::class)->execute($timetable->id);
}
```

**معيار قبول:**
- الحذف من UI يفعّل Traits/Observers
- لا يوجد أي `->delete()` على Query Builder لمسارات الحذف

---

## PR-3: Attendance Becomes Timetable-Aware

**هدف:** كل تسجيل حضور يكتب `timetable_id` ويستخدمه كـ identity

**المبدأ:** الهوية الصريحة أفضل من الهوية الضمنية.

**الخطر لو تركته:** attendance records غير مرتبطة بجدولها الفعلي.

### 1) تحديث RecordStudentAttendanceAction

**ملف:** `app/Domains/Academic/Attendance/Actions/RecordStudentAttendanceAction.php`

```php
// قبل (❌)
Attendance::updateOrCreate([
    'student_id' => $studentId,
    'date' => $date,
    'time_slot_id' => $timetable->time_slot_id,
], [...]);

// بعد (✅)
Attendance::updateOrCreate([
    'student_id' => $studentId,
    'date' => $date,
    'timetable_id' => $timetable->id, // ✅ NEW IDENTITY
], [
    'time_slot_id' => $timetable->time_slot_id,
    'status' => $status,
    'notes' => $notes,
]);
```

### 2) Guard تشغيلي

```php
if (!$timetable) {
    throw \App\Infrastructure\Exceptions\ResourceNotFoundException::make(
        'Timetable entry not found'
    );
}
```

### 2b) Post-Backlog: NOT NULL Constraint

> **ملاحظة:** لضمان uniqueness فعّال، يُنصح لاحقًا بإضافة:
> ```sql
> ALTER TABLE attendances ALTER COLUMN timetable_id SET NOT NULL;
> ```
> مع backfill للسجلات الموجودة أولاً.

**معيار قبول:**
- لا يمكن إنشاء سجل حضور بدون timetable_id
- unique `(student_id, date, timetable_id)` يمنع التكرار فعلياً

---

## PR-4: Calendar Year-Aware + Range Events

**هدف:** التقويم لا يعتمد على active year فقط + Events range-aware

**المبدأ:** التقويم يفهم السياقات المختلفة (سنة/ترم/نطاق).

**ملاحظة تحسين أداء:**
> يمكن تحويل فحص Range إلى exists query بدل تحميل الكل:
> ```php
> SchoolEvent::forYear($yearId)->holidays()
>     ->whereDate('start_date', '<=', $date)
>     ->whereDate('end_date', '>=', $date)
>     ->exists();
> ```

### 1) تحديث SchoolCalendarService

**ملف:** `app/Domains/Academic/Calendar/Services/SchoolCalendarService.php`

```php
/**
 * الحصول على أيام نهاية الأسبوع لسنة محددة
 */
public function getWeekendDaysForYear(int $yearId): array
{
    $year = \App\Domains\Academic\AcademicYear\Models\AcademicYear::find($yearId);
    return $year?->weekend_days ?? [0, 6];
}

/**
 * الحصول على أحداث لسنة محددة
 */
public function getEventsForYear(int $yearId): Collection
{
    return Cache::remember("school_events_year_{$yearId}", 3600, function () use ($yearId) {
        return \App\Domains\Academic\Calendar\Models\SchoolEvent::forYear($yearId)->get();
    });
}

/**
 * التحقق من عطلة لسنة محددة (Range-aware)
 */
public function isHolidayForYear($date, int $yearId): bool
{
    // 1. التحقق من weekend days
    $weekendDays = $this->getWeekendDaysForYear($yearId);
    $dayOfWeek = (int) $date->format('w');
    if (in_array($dayOfWeek, $weekendDays)) {
        return true;
    }

    // 2. التحقق من holidays (Range-aware)
    $events = $this->getEventsForYear($yearId);
    
    foreach ($events as $event) {
        // ⚠️ استخدم اسم العمود الفعلي في schema (event_type, is_holiday, أو category)
        $isHoliday = $event->event_type ?? $event->is_holiday ?? null;
        if ($isHoliday) {
            // التحقق: هل التاريخ داخل Range؟ (شامل - inclusive)
            $start = \Carbon\Carbon::parse($event->start_date)->startOfDay();
            $end = \Carbon\Carbon::parse($event->end_date)->endOfDay();
            $checkDate = $date->copy()->startOfDay();
            
            if ($checkDate->gte($start) && $checkDate->lte($end)) {
                return true;
            }
        }
    }

    return false;
}
```

**معيار قبول:**
- عند عرض/بناء جدول لسنة غير نشطة → التقويم يحسب العطل/الويكند لتلك السنة
- التحقق من العطلة يستخدم Range (`start_date <= date <= end_date`)

---

## PR-5: Attendance Holiday Check Uses Correct Year

**هدف:** منع حضور في عطلة/ويكند "بالسنة الصحيحة"

**المبدأ:** Fail Fast - منع الخاطئ قبل写入 DB.

### مصدر السنة في هذا السياق

> **قاعدة:** `academicYearId` يأتي من `timetable->term->academic_year_id` (وليس activeYear).

**ملف:** `app/Domains/Academic/Attendance/Actions/RecordStudentAttendanceAction.php`

```php
// قبل (❌)
if ($calendarService->isHoliday($date)) {
    throw BusinessRuleException::make('Cannot record attendance on a holiday');
}

// بعد (✅)
$academicYearId = $timetable->term->academic_year_id;
if ($calendarService->isHolidayForYear($date, $academicYearId)) {
    throw BusinessRuleException::make(
        "Cannot record attendance on a holiday for year {$academicYearId}"
    );
}
```

**ملف:** `app/Livewire/Teacher/AttendanceTaker.php`

```php
// نفس التوحيد
if ($calendarService->isHolidayForYear($date, $this->selectedYearId)) {
    $this->dispatch('error', message: __('attendance.holiday_error'));
    return;
}
```

**معيار قبول:**
- لو active year ≠ timetable's year → ما زال يمنع الحضور بشكل صحيح

---

## PR-6: WeeklyTimetable Respects weekend_days

**هدف:** إزالة الهاردكود من أيام الأسبوع

**المبدأ:** البيانات تُأتي من مصدر واحد (SSOT).

### مصدر السنة في هذا السياق

> **قاعدة:** 
> - إذا الشاشة مرتبطة بـ term/section → استخدم yearId منها
> - إذا شاشة عامة/عرض عام → استخدم activeYear()

**ملف:** `app/Livewire/Teacher/WeeklyTimetable.php`

```php
public function mount()
{
    $context = app(\App\Infrastructure\Context\AcademicContextService::class);
    
    // مصدر السنة: من سياق الشاشة (term/section) أو activeYear كـ fallback
    $activeYear = $context->activeYear();
    
    if ($activeYear) {
        // استخراج weekend days من السنة
        $weekendDays = $activeYear->weekend_days ?? [0, 6];
        
        // حساب أيام الدوام (العكس من weekend)
        $this->workingDays = array_values(array_diff([0, 1, 2, 3, 4, 5, 6], $weekendDays));
        
        // بداية ونهاية الأسبوع = أول وآخر يوم دوام
        $this->weekStartDay = min($this->workingDays);
        $this->weekEndDay = max($this->workingDays);
    }
}
```

**ملاحظة:** إذا سياسة المدرسة تبدأ الأسبوع من يوم معين بغض النظر عن الدوام، يمكن تعديل هذا السلوك هنا.

**معيار قبول:**
- تغيير `academic_years.weekend_days` يغيّر عرض الأسبوع بدون تعديل كود

---

## PR-7: CourseOffering Policy Decision

**هدف:** منع "تعديل الماضي" بدون قصد

**المبدأ:** التغيير له عواقب - احذر من الآثار الجانبية.

### الحارس المُصحّح

**ملف:** `app/Domains/Academic/Timetable/Actions/AssignSessionAction.php`

```php
// البحث عن offering موجود
$existingOffering = CourseOffering::where([
    'academic_year_id' => $yearId,
    'subject_id' => $subjectId,
    'class_section_id' => $sectionId,
])->first();

// إذا Offering موجود ويراد تغيير teacher_id
if ($existingOffering && $existingOffering->teacher_id !== $teacherId) {
    // ✅ التحقق: هل هذا Offering مرتبط بـ attendance في term الحالي؟
    $hasAttendance = Timetable::where('course_offering_id', $existingOffering->id)
        ->where('term_id', $termId)
        ->whereHas('attendances') // ✅ علاقة Timetable -> attendances
        ->exists();
    
    if ($hasAttendance) {
        throw \App\Infrastructure\Exceptions\BusinessRuleException::make(
            "Cannot change teacher for a course offering that has attendance records. " .
            "Please create a new course offering for the new teacher."
        );
    }
}

// إنشاء/تحديث Offering
$courseOffering = CourseOffering::updateOrCreate(
    [...],
    ['teacher_id' => $teacherId, 'term_id' => $termId]
);
```

**معيار قبول:**
- تعديل حصة واحدة لا يغيّر حصص أخرى "بالخفاء"
- الحارس يفحص attendance على مستوى Offering، ليس timetable

---

## اختبارات الحد الأدنى

### 1) Timetable deletion safe

```bash
# Scenario 1: Delete session with attendance
1. Create timetable entry
2. Record attendance for students
3. Try to delete session
4. Result: Blocked with "Cannot delete with X attendance records"

# Scenario 2: Delete session without attendance
1. Create timetable entry
2. Delete session
3. Result: Session deleted, no orphan data
```

### 2) Attendance unique works

```bash
# Scenario: Duplicate attendance
1. Record attendance for student A on date X
2. Try to record again for same student/date/session
3. Result: Second record updates first or is blocked by unique constraint
```

### 3) Year-aware holiday (Range-aware)

```bash
# Scenario: Holiday range
1. Create SchoolEvent with start_date=2024-12-25, end_date=2024-12-27
2. Try to record attendance on 2024-12-26
3. Result: Blocked (date is inside holiday range)
```

### 4) Weekend_days changes UI

```bash
# Scenario: Change weekend days
1. Change academic_years.weekend_days from [0,6] to [5,6]
2. Refresh WeeklyTimetable
3. Result: Week display changes to Sunday-Thursday
```

---

## ملخص التغييرات من النسخة السابقة

| النقطة | المشكلة | الإصلاح |
|--------|---------|---------|
| ترتيب PRs | PR-2 قبل PR-1 (خطأ في الملخص) | PR-1 ثم PR-2 (العلاقات تُعرّف أولًا) |
| DeleteTimetableEntryAction | باراميتر غلط ($template->id) | استخدام $timetable->id |
| DeleteTimetableTemplateAction | لا يوجد term scope | إضافة where('term_id', $termId) |
| العلاقات | بدون foreign key | إضافة 'timetable_id' في hasMany |
| Calendar isHoliday | between() غير شامل | استخدام gte/lte للتحقق inclusive |
| Calendar services | افتراض namespace غير صحيح | إزالة افتراض المسار، استخدم البحث الفعلي |
| WeeklyTimetable | weekend_days غلط | حساب workingDays من weekend_days |
| CourseOffering Policy | فحص attendance على timetable خطأ | فحص attendance على Offering |

---

**Document Version:** 2.1 (منقّحة + مبادئ تعليمية)
**Last Updated:** 2026-02-04
**Author:** Kilo Code AI - Based on User Specifications

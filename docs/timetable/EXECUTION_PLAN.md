# 🚀 Timetable + Attendance Integrity Plan (Unified)

**هدف الخطة:** منع ضياع/تزوير البيانات التاريخية، توحيد المرجع بين الجدول والحضور، ومنع أي تعديل يقطع السجل بعد التشغيل الفعلي.

---

## 🔴 الخطوط الحمراء (Non-Negotiables)

### 1) تجميد التاريخ (Data Immutability)
* أي **Timetable له Attendance واحد** يصبح **ReadOnly**:
  * ممنوع تغيير: `course_offering_id` / المعلم / المادة / الوقت / حذف الحصة
  * التغيير يتم بطريقتين واعيتين:
    1. حذف الحضور أولاً (عملية واعية موثقة)
    2. إنشاء "نسخة" جدول جديدة (Versioning) بدل تعديل القديم

### 2) فخ ولادة ID الجديد (ID Rebirth Trap)
* ممنوع `delete() ثم create()` في كيانات "أب" لديها أبناء:
  * Template/TimeSlot/Timetable
* التعديل يكون عبر:
  * `update()` / `updateOrCreate()` على نفس السجل
  * أو "أرشفة" بدلاً من حذف

### 3) المعلم لا يتواجد بمكانين (Teacher Conflict)
* أي تغيير في وقت TimeSlot أو ربطه يجب أن يطلق **Conflict Check** في الـ Action نفسه
* حماية عميقة: لا تقبل إدخال/تعديل يولد تعارض حتى لو الواجهة فاتتها الحالة

### 4) التسلسل الهرمي للنزاهة المرجعية
**Hierarchy:**
```
Template → TimeSlots → Timetables → (Attendances, Substitutions)
```

**القاعدة:**
* لا يُحذف الأب إذا كان له أبناء "نشطون" أو تاريخيون
* في البيانات التاريخية (Attendance) **Cascade Delete ممنوع**
* في substitutions يمكن cascade إذا كانت تابعة بالكامل لـ timetable و"غير تاريخية"

### 5) فخ TimeZone و Date Casts
* أوقات الحصص **ليست تواريخ**: الأفضل حفظها `TIME` أو `string 'H:i'`
* المقارنات تكون على `H:i` مباشرة أو `whereTime()` في SQL
* عند تحويلها لـ Carbon: ثبّت تاريخ موحد افتراضيًا

---

## ✅ التصحيحات الخفية (مدمجة في الخطة)

### (B1) Preflight لا يعتمد على timetable_id
* Phase 0 لا تستخدم استعلامات تعتمد على عمود غير موجود

### (B2) Term Awareness يتحقق من النطاق الزمني
* لا يكفي `term_id is null` - لازم أيضًا:
  * date خارج نطاق term (`date < start_date OR date > end_date`)

### (B3) Unique Constraint جديد للحضور
* الـ upsert يكون على `(student_id, date, timetable_id)`
* لازم DB-level unique index

### (B4) CourseOffering Split-Brain
* CourseOffering يمثل (subject+section+term) وله عدة حصص
* تعديل معلم لحصة واحدة قد يغيّر معلم 4 حصص ثانية
* **الحل:** فصل Assignment عن Offering أو منع تعديل teacher_id إذا مستخدم

### (B5) Backfill قد يعمل Table Lock
* لا تستخدم UPDATE واحد ضخم
* نفذ backfill بالـ chunks عبر Command/Job

### (B6) Cache tags ليس دائمًا متاح
* إن لم تكن على Redis/Memcached: استخدم مفاتيح مفصّلة أو versioning keys

---

## 🚀 خطة التنفيذ النهائية (Phases 0–5)

---

## Phase 0 — Preflight "Stop Report"

**هدف:** معرفة الواقع الفعلي للبيانات ومنع تلويث التاريخ.

### 0.1 Queries (لا تعتمد على timetable_id)

**Query 1: Duplicate Timetable Keys**
```sql
SELECT class_section_id, time_slot_id, term_id, COUNT(*) as cnt
FROM timetables
GROUP BY class_section_id, time_slot_id, term_id
HAVING cnt > 1;
```

**Query 2: Substitutions Orphans**
```sql
SELECT COUNT(*) as count
FROM substitutions s
LEFT JOIN timetables t ON t.id = s.timetable_id
WHERE s.timetable_id IS NOT NULL AND t.id IS NULL;
```

**Query 3: Attendance term orphan**
```sql
SELECT COUNT(*)
FROM attendances a
LEFT JOIN terms t ON t.id = a.term_id
WHERE a.term_id IS NOT NULL AND t.id IS NULL;
```

**Query 4: Attendance date خارج نطاق term** ⚠️ (مهم)
```sql
SELECT COUNT(*)
FROM attendances a
JOIN terms t ON t.id = a.term_id
WHERE a.date < t.start_date OR a.date > t.end_date;
```

**Query 5: TimeSlots مستخدمة**
```sql
SELECT ts.id, ts.label, COUNT(t.id) as timetable_count
FROM time_slots ts
JOIN timetables t ON t.time_slot_id = ts.id
GROUP BY ts.id, ts.label
HAVING COUNT(t.id) > 0;
```

### معيار النجاح (Phase 0)
* [ ] كل النتائج موثقة في `docs/timetable/DATA_AUDIT.md`
* [ ] أي duplicates أو orphans = **توقف** + تنظيف قبل أي migration

---

## Phase 1 — توحيد المرجع: Attendance ↔ Timetable

### 1.1 Migration: إضافة timetable_id + indexes

**المسار:** `database/migrations/2026_02_XX_add_timetable_id_to_attendances.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. إضافة timetable_id كـ nullable
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('timetable_id')
                ->nullable()
                ->after('time_slot_id')
                ->constrained()
                ->nullOnDelete();
        });

        // 2. إضافة indexes
        Schema::table('attendances', function (Blueprint $table) {
            $table->index('timetable_id');
            $table->index(['class_section_id', 'term_id', 'date']);
            // 3. Unique constraint (student, date, timetable)
            $table->unique(
                ['student_id', 'date', 'timetable_id'], 
                'attendance_student_date_timetable_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendance_student_date_timetable_unique');
            $table->dropIndex(['class_section_id', 'term_id', 'date']);
            $table->dropIndex(['timetable_id']);
            $table->dropForeign(['timetable_id']);
            $table->dropColumn('timetable_id');
        });
    }
};
```

### 1.2 Post-Migration Check
```sql
SELECT COUNT(*) 
FROM attendances
WHERE timetable_id IS NULL AND time_slot_id IS NOT NULL;
```

### 1.3 Backfill (Safe & Chunked)

**ملاحظة:** يجب تنفيذ Backfill يدوياً بعد Phase 0

```php
<?php

namespace Database\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillAttendanceTimetableId extends Command
{
    protected $signature = 'timetable:backfill-attendance-timetable-id {--chunk=1000}';
    protected $description = 'Backfill timetable_id for attendances safely';

    public function handle(): int
    {
        $chunk = (int) $this->option('chunk');
        $totalProcessed = 0;
        $totalErrors = 0;

        do {
            // Process in chunks to avoid table lock
            $result = DB::statement("
                UPDATE attendances
                SET timetable_id = (
                    SELECT t.id
                    FROM timetables t
                    WHERE t.class_section_id = attendances.class_section_id
                    AND t.time_slot_id = attendances.time_slot_id
                    AND t.term_id = attendances.term_id
                    LIMIT 1
                )
                WHERE timetable_id IS NULL
                AND time_slot_id IS NOT NULL
                AND (
                    SELECT COUNT(*)
                    FROM timetables t2
                    WHERE t2.class_section_id = attendances.class_section_id
                    AND t2.time_slot_id = attendances.time_slot_id
                    AND t2.term_id = attendances.term_id
                ) = 1
                LIMIT ?
            ", [$chunk]);

            $affected = DB::affectedRows(); // Or use separate select
            $totalProcessed += $affected;

            $this->info("Processed {$affected} records...");

            // Check for ambiguous matches
            $ambiguous = DB::scalar("
                SELECT COUNT(*)
                FROM attendances a
                WHERE a.timetable_id IS NULL
                AND a.time_slot_id IS NOT NULL
                AND (
                    SELECT COUNT(*)
                    FROM timetables t
                    WHERE t.class_section_id = a.class_section_id
                    AND t.time_slot_id = a.time_slot_id
                    AND t.term_id = a.term_id
                ) > 1
            ");

            if ($ambiguous > 0) {
                $this->error("Found {$ambiguous} records with multiple timetable matches!");
                $totalErrors += $ambiguous;
                break;
            }

        } while ($affected > 0);

        $this->info("Total processed: {$totalProcessed}");
        if ($totalErrors > 0) {
            $this->error("Total errors: {$totalErrors}");
            return 1;
        }

        return 0;
    }
}
```

### 1.4 تحديث Model و Action

**[`app/Domains/Academic/Attendance/Models/Attendance.php`](app/Domains/Academic/Attendance/Models/Attendance.php)**

```php
protected $fillable = [
    'student_id',
    'class_section_id',
    'academic_year_id', // ✅ PR1
    'term_id',
    'date',
    'time_slot_id',     // للرجوع فقط
    'timetable_id',     // ✅ المرجع الأساسي
    'status',
    'remarks',
    'delay_minutes',
    'recorded_by',
];

public function timetable(): BelongsTo
{
    return $this->belongsTo(Timetable::class);
}
```

**[`app/Domains/Academic/Attendance/Actions/RecordStudentAttendanceAction.php`](app/Domains/Academic/Attendance/Actions/RecordStudentAttendanceAction.php)**

```php
public function execute(Timetable $timetable, string $date, array $studentsData, int $recordedByUserId): void
{
    // ... existing guards ...

    DB::transaction(function () use ($timetable, $date, $studentsData, $recordedByUserId) {
        foreach ($studentsData as $data) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $data['student_id'],
                    'date' => $date,
                    'timetable_id' => $timetable->id, // ✅ المرجع الأساسي
                ],
                [
                    'class_section_id' => $timetable->class_section_id,
                    'academic_year_id' => $timetable->classSection->academic_year_id,
                    'term_id' => $timetable->term_id,
                    'time_slot_id' => $timetable->time_slot_id, // للرجوع فقط
                    'status' => $data['status'],
                    'delay_minutes' => ($data['status'] === AttendanceStatus::LATE->value) ? ((int) $data['delay_minutes']) : 0,
                    'remarks' => $data['remarks'] ?? null,
                    'recorded_by' => $recordedByUserId,
                ]
            );
        }
    });
}
```

### معيار النجاح (Phase 1)
- [ ] Migration تم تطبيقها بنجاح
- [ ] Unique constraint موجود ويعمل
- [ ] Backfill تم بدون أخطاء
- [ ] كل Attendance جديد مرتبط بـ timetable_id

---

## Phase 2 — منع أي تعديل/حذف يقطع التاريخ

### 2.1 TimetableBuilder::deleteSession

**[`app/Livewire/Academic/TimetableBuilder.php`](app/Livewire/Academic/TimetableBuilder.php)**

```php
public function deleteSession($slotId)
{
    if ($this->isReadOnly) {
        $this->dispatch('error', message: __('attendance.timetable_read_only'));
        return;
    }

    $activeTermId = app(AcademicContextService::class)->activeTerm()?->id;
    if ($this->selectedTermId && $activeTermId && $this->selectedTermId != $activeTermId) {
        abort(403, 'Modification of non-active terms is restricted.');
    }

    // ✅ جلب الـ Model
    $timetable = Timetable::where('class_section_id', $this->selectedSectionId)
        ->where('time_slot_id', $slotId)
        ->where('term_id', $this->selectedTermId)
        ->first();

    if (!$timetable) {
        $this->dispatch('error', message: 'الحصة غير موجودة');
        return;
    }

    // ✅ التحقق من وجود حضور (المرجعية الأساسية: timetable_id)
    $attendanceCount = Attendance::where('timetable_id', $timetable->id)->count();
    if ($attendanceCount > 0) {
        $this->dispatch('error', message: 
            "لا يمكن حذف هذه الحصة لوجود {$attendanceCount} سجل حضور. " .
            "يرجى حذف سجلات الحضور أولاً.");
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

    // ✅ Model delete() لإطلاق Events
    $timetable->delete();

    $this->dispatch('notify', message: 'تم حذف الحصة بنجاح', type: 'success');
}
```

### 2.2 AssignSessionAction (الأهم - CourseOffering Split-Brain)

**[`app/Domains/Academic/Timetable/Actions/AssignSessionAction.php`](app/Domains/Academic/Timetable/Actions/AssignSessionAction.php)**

```php
public function execute(
    int $yearId,
    int $termId,
    int $sectionId,
    int $subjectId,
    int $teacherId,
    int $slotId
): Timetable {
    return DB::transaction(function () use ($yearId, $termId, $sectionId, $subjectId, $teacherId, $slotId) {
        $section = ClassSection::findOrFail($sectionId);
        $slot = TimeSlot::with('template')->findOrFail($slotId);

        // ✅ التحقق من Template
        if ($slot->template) {
            $isValid = DB::table('grade_timetable_template')
                ->where('grade_id', $section->grade_id)
                ->where('academic_year_id', $yearId)
                ->where('template_id', $slot->template->id)
                ->exists();

            if (!$isValid) {
                throw InvalidOperationException::make(
                    'الحصة المحددة غير مرتبطة بالقالب الصحيح لهذه الشعبة.'
                );
            }
        }

        // ✅ CourseOffering ثابت (subject + section + term)
        $courseOffering = CourseOffering::updateOrCreate(
            [
                'academic_year_id' => $yearId,
                'subject_id' => $subjectId,
                'class_section_id' => $sectionId,
            ],
            [
                'term_id' => $termId,
            ]
        );

        // ⚠️ لا نغير teacher_id هنا أبداً!
        // المعلم يُحدد في Timetable مباشرة، أو في علاقة Assignment منفصلة

        // ✅ التحقق من timetable موجود
        $existingTimetable = Timetable::where('class_section_id', $sectionId)
            ->where('time_slot_id', $slotId)
            ->where('term_id', $termId)
            ->first();

        if ($existingTimetable) {
            // ✅ تجميد التاريخ: إذا له حضور -> ممنوع التغيير
            $attendanceCount = Attendance::where('timetable_id', $existingTimetable->id)->count();
            if ($attendanceCount > 0) {
                throw InvalidOperationException::make(
                    "لا يمكن تعديل هذه الحصة لوجود {$attendanceCount} سجل حضور. " .
                    "الحصص التي لها حضور مسجل تكون للقراءة فقط."
                );
            }
        }

        return Timetable::updateOrCreate(
            [
                'class_section_id' => $sectionId,
                'time_slot_id' => $slotId,
                'term_id' => $termId,
            ],
            [
                'course_offering_id' => $courseOffering->id,
                // teacher_id يُضاف هنا إذا كان مطلوباً
            ]
        );
    });
}
```

### معيار النجاح (Phase 2)
- [ ] لا يمكن حذف حصة لها حضور
- [ ] لا يمكن حذف حصة لها بدائل
- [ ] لا يمكن تغيير course_offering لحصة لها حضور
- [ ] CourseOffering لا يغير teacher_id تلقائياً

---

## Phase 3 — حماية Templates/Slots

### 3.1 UpdateTimetableTemplateAction

**[`app/Domains/Academic/Timetable/Actions/UpdateTimetableTemplateAction.php`](app/Domains/Academic/Timetable/Actions/UpdateTimetableTemplateAction.php)**

```php
public function execute(TimetableTemplate $template, TimetableTemplateData $data): TimetableTemplate
{
    if (!$template->isEditable()) {
        throw new TemplateNotEditableException($template->id, $template->status);
    }

    // ✅ التحقق من وجود حصص timetable مرتبطة
    $linkedTimetableCount = Timetable::whereHas('timeSlot', function ($q) use ($template) {
        $q->where('template_id', $template->id);
    })->count();

    if ($linkedTimetableCount > 0) {
        throw InvalidOperationException::make(
            "لا يمكن تحديث القالب لوجود {$linkedTimetableCount} حصص جدول مرتبطة. " .
            "يرجى حذف الحصص المرتبطة أولاً أو إنشاء قالب جديد."
        );
    }

    return DB::transaction(function () use ($template, $data) {
        $template->update($data->toModelArray());

        // ✅ Update slots instead of delete+create
        foreach ($data->slots as $slotData) {
            $slotArray = ($slotData instanceof TimeSlotData)
                ? $slotData->toModelArray()
                : $slotData;

            $template->timeSlots()->updateOrCreate(
                [
                    'id' => $slotArray['id'] ?? null,
                ],
                $slotArray
            );
        }

        // ... rest of update ...
    });
}
```

### 3.2 DeleteTimetableTemplateAction

```php
public function execute(TimetableTemplate $template): void
{
    if ($template->status === TemplateStatus::Active) {
        throw new TemplateNotEditableException(
            $template->id,
            $template->status,
            "لا يمكن حذف قالب نشط. قم بأرشفته أولاً."
        );
    }

    // ✅ استخدام canDelete() من Trait
    if (!$template->canDelete()) {
        $blockers = $template->getDeletionBlockers();
        throw new CannotDeleteException(
            "لا يمكن حذف القالب لوجود بيانات مرتبطة:\n" .
            implode("\n", array_map(fn($b) => "• {$b}", $blockers))
        );
    }

    $template->delete();
}
```

### معيار النجاح (Phase 3)
- [ ] لا يمكن تحديث قالب له timetable مرتبط
- [ ] لا يمكن حذف قالب محمي
- [ ] التحديث يستخدم updateOrCreate بدلاً من delete+create

---

## Phase 4 — Term Awareness (PR1)

### 4.1 إضافة Term::forDate

**[`app/Domains/Academic/Term/Models/Term.php`](app/Domains/Academic/Term/Models/Term.php)**

```php
public static function forDate(Carbon $date): ?self
{
    return static::where('start_date', '<=', $date)
        ->where('end_date', '>=', $date)
        ->active()
        ->first();
}
```

### 4.2 Attendance Year + Term Fillable

**[`app/Domains/Academic/Attendance/Models/Attendance.php`](app/Domains/Academic/Attendance/Models/Attendance.php)**

```php
protected $fillable = [
    'student_id',
    'class_section_id',
    'academic_year_id', // ✅
    'term_id',          // ✅
    'date',
    'time_slot_id',
    'timetable_id',
    'status',
    'remarks',
    'delay_minutes',
    'recorded_by',
];
```

### 4.3 weekend_days Cast

**[`app/Domains/Academic/AcademicYear/Models/AcademicYear.php`](app/Domains/Academic/AcademicYear/Models/AcademicYear.php)**

```php
protected $casts = [
    'start_date' => 'date',
    'end_date' => 'date',
    'weekend_days' => 'array', // ✅
    'status' => AcademicYearStatus::class,
];
```

### 4.4 Calendar Cache Year-Aware

**[`app/Domains/Academic/Calendar/Services/SchoolCalendarService.php`](app/Domains/Academic/Calendar/Services/SchoolCalendarService.php)**

```php
public function isHoliday(Carbon $date, ?int $yearId = null): bool
{
    $yearId = $yearId ?? school()->activeYearId();
    
    // ✅ Use year-specific key
    return Cache::remember(
        "calendar.holiday.{$yearId}.{$date->format('Y-m-d')}",
        3600,
        fn() => $this->checkHoliday($date, $yearId)
    );
}
```

### معيار النجاح (Phase 4)
- [ ] Term::forDate(Carbon::now()) يعمل صحيح
- [ ] academic_year_id يُحفظ في Attendance
- [ ] weekend_days يُعامل كـ array
- [ ] كاش التقويم يُفرق بين السنوات

---

## Phase 5 — Medium Fixes

### 5.1 TimeSlot Time Casts

**[`app/Domains/Academic/Timetable/Models/TimeSlot.php`](app/Domains/Academic/Timetable/Models/TimeSlot.php)**

```php
protected $casts = [
    'day_of_week' => 'integer',
    'order_index' => 'integer',
    'start_time' => 'string', // 'H:i' format
    'end_time' => 'string',   // 'H:i' format
    'type' => TimeSlotType::class,
    'is_attendance_checkpoint' => 'boolean',
];

public function getDurationMinutesAttribute(): int
{
    $start = Carbon::createFromFormat('H:i', $this->start_time);
    $end = Carbon::createFromFormat('H:i', $this->end_time);
    return $start->diffInMinutes($end);
}

public function getTimeRangeAttribute(): string
{
    return "{$this->start_time} - {$this->end_time}";
}
```

### 5.2 timetableEntriesForTerm

```php
public function timetableEntriesForTerm(int $termId): HasMany
{
    return $this->hasMany(Timetable::class)->where('term_id', $termId);
}
```

---

## 🧪 الاختبارات (Acceptance Tests)

1. ✅ لا يمكن حذف timetable له attendance
2. ✅ لا يمكن تغيير course_offering لحصة لها attendance
3. ✅ لا يمكن تحديث template إذا لديه timetables مرتبطة
4. ✅ لا يمكن توليد duplicate attendance لنفس (student, date, timetable)
5. ✅ Past term تعديل/حذف ممنوع، Future term مسموح

---

## 📋 قائمة الملفات المتأثرة

### Models (6)
- `TimeSlot.php`
- `Timetable.php`
- `TimetableTemplate.php`
- `Attendance.php`
- `Term.php`
- `AcademicYear.php`

### Actions (4)
- `UpdateTimetableTemplateAction.php`
- `DeleteTimetableTemplateAction.php`
- `AssignSessionAction.php`
- `RecordStudentAttendanceAction.php`

### Livewire (2)
- `TimetableBuilder.php`
- `AttendanceTaker.php`

### Services (2)
- `TimetableService.php`
- `SchoolCalendarService.php`

### Migrations (1)
- `2026_02_XX_add_timetable_id_to_attendances.php`

---

**تاريخ المستند:** 2026-02-04
**الإصدار:** Final Unified Plan
**الحالة:** Ready for Execution

# PR2 Gate 1 — Audit Report: Timetable Template Update Safety
**تاريخ التقرير:** 2026-02-04  
**المصدر:** MCP Laravel Database Queries + Codebase Analysis

---

## 📊 الوضع الفعلي في قاعدة البيانات (Real Database State)

### البيانات الحالية:
- **القوالب:** 1 قالب نشط (`active`)
- **الحصص:** 45 حصة مرتبطة بالقالب النشط
- **الجداول:** 1 جدول (`timetable`) يستخدم حصة واحدة من القالب
- **الحضور:** 0 سجلات حضور حالياً (لكن النظام جاهز)
- **الترم:** القالب مرتبط بترم واحد (term_id = 2)

### الاستخدام الفعلي:
```
Template ID: 1 (نشط)
├── 45 time_slots
├── 1 timetable entry (يستخدم slot_id = 5)
└── 1 term (term_id = 2)
```

---

## 🔍 1. أين تُدار القوالب (Template Management Points)

### 1.1 UI Layer (Livewire)
**الملف:** `app/Livewire/Timetable/TimetableTemplateManager.php`

**العمليات:**
- `create()` — إنشاء قالب جديد
- `edit($id)` — تعديل قالب موجود
- `save()` — حفظ (يستدعي `CreateTimetableTemplateAction` أو `UpdateTimetableTemplateAction`)
- `activate($id)` — تفعيل القالب
- `archive($id)` — أرشفة القالب
- `delete()` — حذف القالب (يستدعي `DeleteTimetableTemplateAction`)
- `duplicate($id)` — نسخ القالب

**Route:** `GET|HEAD timetable-templates` → `timetable-templates.index`

### 1.2 Domain Actions
- `CreateTimetableTemplateAction.php` — إنشاء قالب جديد
- `UpdateTimetableTemplateAction.php` ⚠️ **نقطة حرجة**
- `DeleteTimetableTemplateAction.php` — حذف قالب
- `ActivateTimetableTemplateAction.php` — تفعيل
- `ArchiveTimetableTemplateAction.php` — أرشفة
- `DuplicateTimetableTemplateAction.php` — نسخ

### 1.3 Service Layer
**الملف:** `app/Domains/Academic/Timetable/Services/TimetableTemplateService.php`

**العمليات:**
- `create()` — إنشاء + syncTimeSlots
- `update()` — تحديث + syncTimeSlots (يحتوي على حماية جزئية)
- `delete()` — حذف (مع حماية للقوالب النشطة)
- `duplicate()` — نسخ
- `applyDayToAllDays()` — تطبيق حصص يوم على جميع الأيام ⚠️

---

## ⚠️ 2. النقاط الحرجة: أين يتم حذف Time Slots

### 2.1 **CRITICAL ISSUE #1: UpdateTimetableTemplateAction**

**الملف:** `app/Domains/Academic/Timetable/Actions/UpdateTimetableTemplateAction.php`  
**السطر:** 40

```php
// تحديث الحصص
$template->timeSlots()->delete(); // ⚠️ حذف كامل بدون أي تحقق من الاستخدام!
foreach ($data->slots as $slotData) {
    $template->timeSlots()->create($slotArray);
}
```

**المشكلة:**
- ❌ لا يتحقق من وجود جداول تستخدم القالب
- ❌ يعتمد فقط على `isEditable()` الذي يتحقق من الـ status فقط
- ❌ لا يستخدم `TimetableTemplateService::syncTimeSlots()` الذي يحتوي على حماية جزئية

**التأثير:**
- إذا كان القالب `Draft` لكن مستخدم في جداول → سيحذف الحصص ويحذف الجداول عبر cascade
- إذا تم أرشفة قالب نشط ثم محاولة تعديله → سيحذف الحصص بدون حماية

### 2.2 **CRITICAL ISSUE #2: TimetableTemplateService::syncTimeSlots()**

**الملف:** `app/Domains/Academic/Timetable/Services/TimetableTemplateService.php`  
**السطر:** 283

```php
private function syncTimeSlots(TimetableTemplate $template, array $slots): void
{
    // التحقق من عدم وجود جداول نشطة تستخدم القالب (إذا كان القالب نشط)
    if ($template->status === TemplateStatus::Active) {
        $activeTimetables = Timetable::whereHas('timeSlot', function($q) use ($template) {
            $q->where('template_id', $template->id);
        })->exists();

        if ($activeTimetables) {
            throw new TemplateNotEditableException(...);
        }
    }

    // حذف الحصص القديمة
    $template->timeSlots()->delete(); // ⚠️ حذف كامل
    // ...
}
```

**المشكلة:**
- ✅ يتحقق من الاستخدام للقوالب النشطة فقط
- ❌ لا يتحقق من الاستخدام للقوالب المسودة (`Draft`)
- ❌ لا يتحقق من الاستخدام للقوالب المؤرشفة (`Archived`)

**الثغرة:**
- قالب `Draft` مستخدم في جداول → يمكن تعديله وحذف الحصص
- قالب `Archived` مستخدم في بيانات تاريخية → يمكن تعديله وحذف الحصص

### 2.3 **CRITICAL ISSUE #3: applyDayToAllDays()**

**الملف:** `app/Domains/Academic/Timetable/Services/TimetableTemplateService.php`  
**السطر:** 182-184

```php
// حذف حصص باقي الأيام
$template->timeSlots()
    ->where('day_of_week', '!=', $sourceDay)
    ->delete(); // ⚠️ حذف انتقائي بدون أي تحقق
```

**المشكلة:**
- ❌ لا يتحقق من الاستخدام قبل الحذف
- ❌ قد يحذف حصص مستخدمة في جداول نشطة

### 2.4 **DeleteTimetableTemplateAction**

**الملف:** `app/Domains/Academic/Timetable/Actions/DeleteTimetableTemplateAction.php`

```php
if ($template->status === TemplateStatus::Active) {
    throw new TemplateNotEditableException(...);
}

// حذف الجداول المرتبطة يدوياً
$deletedSessions = Timetable::whereIn('time_slot_id', $slotIds)->delete();
// ثم حذف القالب (time_slots تُحذف تلقائياً عبر cascade)
```

**الوضع:**
- ✅ يمنع حذف القوالب النشطة
- ❌ يسمح بحذف القوالب المسودة/المؤرشفة حتى لو كانت مستخدمة
- ⚠️ يحذف الجداول يدوياً قبل حذف القالب (لكن هذا تدميري)

---

## 🔗 3. خريطة العلاقات FK Cascade (من قاعدة البيانات الفعلية)

### 3.1 العلاقات الهرمية:

```
TimetableTemplate (timetable_templates)
    ↓ cascadeOnDelete
TimeSlot (time_slots)
    ↓ cascadeOnDelete
Timetable (timetables)
    ↓ cascadeOnDelete (class_section)
    ↓ setNull (course_offering)
    ↓ setNull (facility)
Attendance (attendances)
    ↓ setNull (time_slot_id) ⚠️
```

### 3.2 تفاصيل FK Cascade (من Migrations):

#### TimetableTemplate → TimeSlot
- **Migration:** `2025_11_19_186300_create_time_slots_table.php`
- **FK:** `time_slots.template_id → timetable_templates.id`
- **Cascade:** `cascadeOnDelete` ✅
- **النتيجة:** حذف القالب يحذف جميع `time_slots` المرتبطة

#### TimeSlot → Timetable
- **Migration:** `2025_11_19_186500_create_timetables_table.php`
- **FK:** `timetables.time_slot_id → time_slots.id`
- **Cascade:** `cascadeOnDelete` ⚠️ **حرج**
- **النتيجة:** حذف `time_slot` يحذف جميع `timetables` المرتبطة

#### TimeSlot → Attendance
- **FK:** `attendances.time_slot_id → time_slots.id`
- **Cascade:** `setNull` ✅
- **النتيجة:** حذف `time_slot` يجعل `time_slot_id` في `attendances` = `NULL` (لا يحذف سجلات الحضور لكن يفقد الربط)

---

## 📍 4. نطاق القالب (Template Scope)

### 4.1 الربط بالسنة الدراسية
- القالب مرتبط بـ `academic_year_id` (FK إلى `academic_years`)
- Cascade: `cascadeOnDelete` (حذف السنة يحذف القوالب)

### 4.2 الربط بالمرحلة التعليمية
- القالب مرتبط بـ `educational_stage_id` (FK إلى `educational_stages`)
- Cascade: `setNull` (حذف المرحلة لا يحذف القالب)

### 4.3 الربط بالصفوف
- علاقة Many-to-Many عبر `grade_timetable_template`
- قيد Unique: `(grade_id, academic_year_id)` — صف واحد لا يمكن أن يكون مرتبطاً بأكثر من قالب في نفس السنة

### 4.4 الربط بالترم
- ❌ **القالب غير مرتبط مباشرة بالترم**
- القالب مرتبط بالسنة الدراسية فقط
- الجداول (`timetables`) مرتبطة بالترم (`term_id`) — PR0

**النتيجة:** قالب واحد يمكن استخدامه في عدة ترمات (Term 1, Term 2) في نفس السنة

---

## 🛡️ 5. الحماية الحالية (Existing Guards)

### 5.1 TemplateStatus::isEditable()
```php
public function isEditable(): bool
{
    return $this === self::Draft; // فقط المسودة قابلة للتعديل
}
```

**المشكلة:**
- يعتمد فقط على الـ status
- لا يتحقق من الاستخدام الفعلي

### 5.2 حماية في `TimetableTemplateService::syncTimeSlots()`
```php
if ($template->status === TemplateStatus::Active) {
    $activeTimetables = Timetable::whereHas('timeSlot', function($q) use ($template) {
        $q->where('template_id', $template->id);
    })->exists();

    if ($activeTimetables) {
        throw new TemplateNotEditableException(...);
    }
}
```

**المشكلة:**
- ✅ موجودة لكن فقط للقوالب النشطة
- ❌ لا تحمي القوالب المسودة (`Draft`) التي قد تكون مستخدمة
- ❌ لا تحمي القوالب المؤرشفة (`Archived`) التي قد تكون مستخدمة في بيانات تاريخية

### 5.3 حماية في `DeleteTimetableTemplateAction`
```php
if ($template->status === TemplateStatus::Active) {
    throw new TemplateNotEditableException(...);
}
```

**المشكلة:**
- ✅ تمنع حذف القوالب النشطة فقط
- ❌ تسمح بحذف القوالب المسودة/المؤرشفة حتى لو كانت مستخدمة

---

## 🚨 6. المشاكل المكتشفة (Critical Issues)

### Issue #1: UpdateTimetableTemplateAction لا يتحقق من الاستخدام
**الخطورة:** 🔴 **عالية جداً**

- **الموقع:** `UpdateTimetableTemplateAction::execute()` السطر 40
- **المشكلة:** `$template->timeSlots()->delete()` يحذف جميع الحصص بدون أي تحقق
- **السيناريو الكارثي:**
  1. قالب `Draft` مستخدم في جدول نشط
  2. محاولة تعديل القالب → `isEditable()` يعيد `true`
  3. `UpdateTimetableTemplateAction` يحذف جميع `time_slots`
  4. DB cascade يحذف جميع `timetables` المرتبطة
  5. سجلات الحضور تفقد `time_slot_id` (تصبح `NULL`)
  6. Teacher Dashboard و AttendanceTaker ينهاران

### Issue #2: syncTimeSlots() حماية جزئية فقط
**الخطورة:** 🟡 **متوسطة**

- **الموقع:** `TimetableTemplateService::syncTimeSlots()` السطر 283
- **المشكلة:** يتحقق فقط للقوالب النشطة
- **الثغرة:** القوالب المسودة/المؤرشفة يمكن تعديلها حتى لو كانت مستخدمة

### Issue #3: applyDayToAllDays() بدون حماية
**الخطورة:** 🟡 **متوسطة**

- **الموقع:** `TimetableTemplateService::applyDayToAllDays()` السطر 182-184
- **المشكلة:** حذف انتقائي بدون التحقق من الاستخدام

### Issue #4: DeleteTimetableTemplateAction يحذف الجداول يدوياً
**الخطورة:** 🔴 **عالية**

- **الموقع:** `DeleteTimetableTemplateAction::execute()` السطر 35
- **المشكلة:** يحذف الجداول يدوياً قبل حذف القالب
- **التأثير:** تدميري للبيانات التاريخية

---

## 📊 7. خريطة التأثير (Impact Map)

### السيناريو الكارثي المحتمل:

```
1. قالب Draft مستخدم في جدول نشط (term_id = 2)
   ↓
2. الإدارة تعدّل القالب عبر UpdateTimetableTemplateAction
   ↓
3. isEditable() = true (لأن status = Draft)
   ↓
4. timeSlots()->delete() يحذف جميع 45 حصة
   ↓
5. DB cascade يحذف جميع timetables المرتبطة
   ↓
6. attendances.time_slot_id تصبح NULL
   ↓
7. Teacher Dashboard ينهار
   ↓
8. AttendanceTaker لا يعمل
   ↓
9. البيانات التاريخية تفقد المعنى
```

---

## ✅ 8. الخلاصة والتوصية

### الوضع الحالي:
- ✅ يوجد حماية جزئية للقوالب النشطة فقط
- ❌ لا توجد حماية للقوالب المسودة أو المؤرشفة
- ❌ `UpdateTimetableTemplateAction` لا يتحقق من الاستخدام
- ❌ عملية التحديث تدميرية (حذف كامل قبل إعادة الإنشاء)

### التوصية النهائية:
**Option B (Versioning)** هو الأنسب للأسباب التالية:

1. **يحافظ على البيانات التاريخية** — القوالب القديمة تبقى للتاريخ
2. **يسمح بتعديل القوالب بدون كسر البيانات** — نسخة جديدة للترم الجديد
3. **يتوافق مع سيناريوهات المدارس** — تغيير الجدول منتصف السنة (رمضان، اختبارات)
4. **يمنع الثغرات الحالية** — لا يمكن تعديل قالب مستخدم (يجب إنشاء نسخة)

---

## 📁 9. الملفات المرجعية

### Models:
- `app/Domains/Academic/Timetable/Models/TimetableTemplate.php`
- `app/Domains/Academic/Timetable/Models/TimeSlot.php`
- `app/Domains/Academic/Timetable/Models/Timetable.php`

### Actions:
- `app/Domains/Academic/Timetable/Actions/UpdateTimetableTemplateAction.php` ⚠️
- `app/Domains/Academic/Timetable/Actions/DeleteTimetableTemplateAction.php` ⚠️

### Services:
- `app/Domains/Academic/Timetable/Services/TimetableTemplateService.php` ⚠️

### UI:
- `app/Livewire/Timetable/TimetableTemplateManager.php`

### Enums:
- `app/Domains/Academic/Timetable/Enums/TemplateStatus.php`

### Migrations:
- `database/migrations/2025_11_19_186300_create_time_slots_table.php`
- `database/migrations/2025_11_19_186500_create_timetables_table.php`

---

## 🎯 10. Next Steps

بناءً على هذا التقرير، يجب:

1. **اتخاذ القرار النهائي:** Option A (Lock) vs Option B (Versioning) vs Option C (Update-in-place)
2. **تطبيق الحل المختار** في Gate 3
3. **إضافة اختبارات** في Gate 4 للتأكد من عدم الانحدار

---

**Gate 1 Audit Report مكتمل ✅**

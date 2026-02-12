# خطة إصلاح دومين الجدول الدراسي - تاسكات تنفيذ

**المرجع:** هذا الملف يترجم فحص دومين الجدول الدراسي إلى تاسكات تنفيذية دقيقة.  
**مبدأ التنفيذ:** لا تغيير لسلوك يعمل إلا إذا كان يسبب Crash، خلل منطقي مؤكد، أو ضوضاء آمنة للحذف.

---

## نطاق الإصلاح (وفق فحص MCP)

- **Routes ذات الصلة:**  
  - `timetable`  
  - `timetable-templates`  
  - `teacher/timetable`  
  - `teacher/attendance/{timetableId}`

- **جداول قاعدة البيانات الأساسية:**  
  - `timetable_templates`  
  - `time_slots`  
  - `timetables`  
  - `grade_timetable_template`

- **جداول تعتمد على الجدول (للمنع قبل الحذف):**  
  - `attendances` (FK على `timetables`)  
  - `substitutions` (FK على `timetables`)

---

## TT-0 — إصلاح أعطال تشغيل (Crash Safety)

**الهدف:** منع أخطاء مباشرة عند فتح/تعديل القالب أو حفظ الحصص.

- [x] **TimeSlotData::fromModel**: إزالة `->format()` على قيم time التي أصبحت نصية.  
  - الملف: `app/Domains/Academic/Timetable/Data/TimeSlotData.php`

- [x] **TimetableTemplateForm::setModel**: نفس المشكلة في تحويل الوقت.  
  - الملف: `app/Livewire/Forms/Timetable/TimetableTemplateForm.php`

- [x] **TimetableBuilder**: منع الحفظ/الحذف عند عدم اختيار ترم (تجنب TypeError).  
  - الملف: `app/Livewire/Academic/TimetableBuilder.php`

- [x] **TeacherScheduleWidget view**: التعامل مع `start_time` كسلسلة نصية بدل `format()` المباشر.  
  - الملف: `resources/views/livewire/dashboard/teacher-schedule-widget.blade.php`

---

## TT-1 — أمان الحذف (ProtectedRelations + SafeDelete)

**الهدف:** منع حذف حصص لها تبعيات غير مقصودة (بدائل/حضور).

- [x] **TimetableBuilder::deleteSession**: استخدام `DeleteTimetableEntryAction` بدل Query delete.  
  - الملف: `app/Livewire/Academic/TimetableBuilder.php`

---

## TT-2 — Guard الكتابة للسنة/الترم (AcademicWriteGuard)

**الهدف:** منع تعديل أو حذف الجداول في سنة مغلقة/مؤرشفة أو ترم مكتمل.

- [x] إضافة `AcademicWriteGuard` في Actions التالية:  
  - `CreateTimetableTemplateAction`  
  - `UpdateTimetableTemplateAction`  
  - `ActivateTimetableTemplateAction`  
  - `ArchiveTimetableTemplateAction`  
  - `DuplicateTimetableTemplateAction`  
  - `DeleteTimetableTemplateAction`  
  - `DeleteTimetableEntryAction`  
  - `GenerateTimetableAction`

---

## TT-3 — حذف القوالب: إصلاح فلتر غير صحيح (term_id)

**الهدف:** تصحيح مسار الحذف لكي يُحذف الجدول المرتبط فعليًا بالقالب.

- [x] إزالة فلتر `term_id` غير الموجود على القالب.  
  - الملف: `app/Domains/Academic/Timetable/Actions/DeleteTimetableTemplateAction.php`

---

## TT-4 — فلترة جدول المعلم (منع جداول قديمة)

**الهدف:** منع عرض حصص من سنوات/ترم غير نشطة داخل الواجهات.

- [x] إضافة فلترة السنة/الترم النشط في `TeacherScheduleWidget`.  
  - الملف: `app/Livewire/Dashboard/TeacherScheduleWidget.php`

---

## TT-5 — منع تعيين حصص غير قابلة للتعيين

**الهدف:** عدم السماح بتعيين مادة/معلم لحصص استراحة/طابور/صلاة.

- [x] **AssignSessionAction**: تحقق أن `TimeSlot->type` قابل للتعيين.  
  - الملف: `app/Domains/Academic/Timetable/Actions/AssignSessionAction.php`

---

## TT-6 — التحقق من تداخل الحصص (Slot Overlap Validation)

**الهدف:** منع إنشاء قوالب بحصص متداخلة زمنياً.

- [x] استخدام `TimetableSlotValidator` داخل:  
  - `CreateTimetableTemplateAction`  
  - `UpdateTimetableTemplateAction`

---

## TT-8 — أمان الاستبدال + اتساق السنة

**الهدف:** منع فقد بيانات عند الاستبدال وضمان اتساق السنة للشعبة.

- [x] **GenerateTimetableAction**: منع الاستبدال إذا وجدت attendances، والحذف عبر Model delete.  
  - الملف: `app/Domains/Academic/Timetable/Actions/GenerateTimetableAction.php`

- [x] **AssignSessionAction**: منع التعيين إذا كانت الشعبة لا تتبع السنة المختارة.  
  - الملف: `app/Domains/Academic/Timetable/Actions/AssignSessionAction.php`

---

## TT-7 — تنظيف ضوضاء (مشروط بعدم الاستخدام)

**الهدف:** إزالة كود غير مستخدم بدون تأثير سلوكي.

- [x] حذف `TimetableTemplateService` إذا لا توجد أي مراجع له.  
  - الملف: `app/Domains/Academic/Timetable/Services/TimetableTemplateService.php`

- [x] حذف `GenerateTimetableAction` إذا غير مستخدم.  
  - الملف: `app/Domains/Academic/Timetable/Actions/GenerateTimetableAction.php`

---

## اختبارات مستهدفة

- [x] تحديث/إضافة اختبار لـ `TimetableBuilder` عند عدم اختيار ترم (يمنع الحفظ).  
- [x] اختبار أن `TeacherScheduleWidget` لا يعرض إلا الترم النشط.  
- [x] اختبار رفض تعيين مادة في slot غير قابل للتعيين.  
- [x] تشغيل:  
  - [x] `tests/Feature/TimetableBuilderTest.php`  
  - [x] `tests/Feature/Academic/TimetableTermAwarenessTest.php`  
  - [x] `tests/Feature/Academic/TimetableAssignSessionActionTest.php`  
  - [x] `tests/Feature/Livewire/Dashboard/TeacherScheduleWidgetTest.php`

---

## مخرجات متوقعة

- لا انهيار عند فتح/تعديل قالب أو حفظ حصص.  
- لا حذف حصص مرتبطة بحضور/بدائل دون Guard.  
- تطبيق حارس السنة/الترم في كل عمليات الكتابة.  
- فلترة جداول المعلم بالترم النشط.  
- منع تعيين حصص على استراحة/طابور/صلاة.  
- إزالة الضوضاء غير المستخدمة بعد التحقق.

# خطة إصلاح دومين الصفوف والشعب - تاسكات تنفيذ

**المرجع:** هذا الملف يترجم فحص الصفوف والشعب إلى تاسكات تنفيذية دقيقة.  
**مبدأ التنفيذ:** لا تغيير لسلوك يعمل إلا إذا كان يسبب Crash، خلل منطقي مؤكد، أو ضوضاء آمنة للحذف.

---

## نطاق الإصلاح (وفق فحص MCP)

- **Routes ذات الصلة:**  
  - `structure` (المراحل + الصفوف)  
  - `class-sections` (الشعب)
- **جداول قاعدة البيانات:**  
  - `grades` (قيود: `grades_educational_stage_id_name_unique`)  
  - `class_sections` (قيود: `section_unique_constraint`)

---

## GS-0 — نقطة الدخول (Crash Safety)

**الهدف:** التأكد أن صفحات الصفوف والشعب تعمل فعليًا بدون Crash.

- [x] **structure.index** موجود ويقوم بتركيب Livewire.  
  - الملف: `resources/views/structure/index.blade.php`
- [x] **class-sections.index** موجود ويقوم بتركيب Livewire.  
  - الملف: `resources/views/class-sections/index.blade.php`

---

## GS-1 — حماية الحذف (Critical)

**الهدف:** منع حذف صف/شعبة لديها تبعيات (خطر فقد بيانات أو Cascade).

- [x] **ClassSection: تعريف العلاقات الحرجة وإضافتها إلى `protectedRelations`.**  
  - تشمل (على الأقل): `courseOfferings`, `timetables`, `attendances`, `enrollments`, `promotions`, `students`  
  - الملف: `app/Domains/Academic/ClassSection/Models/ClassSection.php`
- [x] **ClassSection: توحيد فحص الحذف برسالة واضحة** بدل فحص الطلاب فقط.  
  - الملف: `app/Domains/Academic/ClassSection/Actions/DeleteClassSectionAction.php`
- [x] **DeleteClassSectionAction: فحص DB مباشر للتبعيات الحرجة قبل الحذف** (حتى لو كانت العلاقات ناقصة).  
  - تشمل (على الأقل): `attendances`, `timetables`, `student_enrollments`, `course_offerings`, `promotions`, `students`  
  - الملف: `app/Domains/Academic/ClassSection/Actions/DeleteClassSectionAction.php`
- [x] **Grade: تثبيت سياسة الحذف الحالية بوضوح** (منع الحذف عند وجود `sections` أو `subjects`).  
  - إن كان هناك ارتباط مباشر فعلي بالـ `student_enrollments` في DB، نضيف منع واضح برسالة مفهومة، وإلا لا نوسّع بدون دليل.  
  - الملف: `app/Domains/Academic/Grade/Models/Grade.php`
  - الملف: `app/Domains/Academic/Grade/Services/GradeService.php`

---

## GS-2 — حارس السنة المغلقة (Write Guard)

**الهدف:** منع تعديل/إنشاء الشعب في سنة مغلقة أو مؤرشفة (Archived).

- [x] إضافة `AcademicWriteGuard` في إنشاء/تعديل/حذف الشعبة (Closed + Archived).  
  - الملفات:  
    - `app/Domains/Academic/ClassSection/Actions/CreateClassSectionAction.php`  
    - `app/Domains/Academic/ClassSection/Actions/UpdateClassSectionAction.php`  
    - `app/Domains/Academic/ClassSection/Actions/DeleteClassSectionAction.php`
- [x] منع النسخ إلى سنة مغلقة أو مؤرشفة.  
  - الملف: `app/Domains/Academic/ClassSection/Actions/CloneClassSectionsAction.php`
- [x] قرار واضح لنسخ الشعب من سنة مغلقة/مؤرشفة (قراءة فقط): **مسموح (قراءة فقط) والـ guard على سنة الهدف فقط**.  
  - الملف: `app/Domains/Academic/ClassSection/Actions/CloneClassSectionsAction.php`
- [x] تحقق أن الـ `grade_id` في النسخ صالح داخل السياق (لا نسخ لشعب بصف غير موجود).  
  - الملف: `app/Domains/Academic/ClassSection/Actions/CloneClassSectionsAction.php`

---

## GS-3 — التحقق من القيود الفعلية (Unique Constraints)

**الهدف:** رسائل واضحة بدل أخطاء قاعدة البيانات.

- [x] **Grade:** تحقق من uniqueness (المرحلة + الاسم).  
  - الملفات:  
    - `app/Livewire/Forms/Academic/GradeForm.php`  
    - `app/Domains/Academic/Grade/Services/GradeService.php`
- [x] **ClassSection:** تحقق من uniqueness (السنة + الصف + الاسم).  
  - الملفات:  
    - `app/Livewire/Forms/Academic/ClassSectionForm.php`  
    - `app/Domains/Academic/ClassSection/Actions/CreateClassSectionAction.php`

---

## GS-4 — توحيد وإبطال الكاش (Cache Policy)

**الهدف:** منع بيانات قديمة في الواجهات والقوائم.

- [x] **GradeLookupService:** إبطال كاش `grades_by_year_{yearId}` عند التعديلات.  
  - الملف: `app/Domains/Academic/Grade/Services/GradeLookupService.php`
- [x] **ClassSectionLookupService:** إبطال كاش القوائم المسطحة و`sections_grade_{gradeId}_year_{yearId}` و`sections_flat_list_year_{yearId}`.  
  - الملف: `app/Domains/Academic/ClassSection/Services/ClassSectionLookupService.php`
- [x] **مركزة إبطال `academic_directory_stats`** داخل Observer أو invalidateCache المركزي بدل تكراره في Actions.  
  - الملف: `app/Domains/Academic/ClassSection/Services/ClassSectionLookupService.php` (أو Observer)

---

## GS-5 — تنظيف ضوضاء آمنة (Safe Cleanup)

**الهدف:** إزالة كود غير مستخدم لتقليل التضارب.

- [x] حذف `ClassSectionService` **فقط إذا كان غير مستخدم فعلياً (search references = 0)**.  
  - الملف: `app/Domains/Academic/ClassSection/Services/ClassSectionService.php`
- [x] حذف `StageLookupService` **فقط إذا كان غير مستخدم فعلياً (search references = 0)**.  
  - الملف: `app/Domains/Academic/Stage/Services/StageLookupService.php`
- [x] إزالة scope غير مستخدم (`scopeWithAvailableCapacity` إذا كان يعتمد على عمود غير موجود).  
  - الملف: `app/Domains/Academic/ClassSection/Traits/ClassSectionScopes.php`

---

## اختبارات مستهدفة

- [x] `tests/Feature/ClassSectionManagerTest.php`  
- [x] `tests/Feature/StructureManagerTest.php`  
- [x] إضافة Test لمنع حذف شعبة لديها تبعيات (timetables/attendances/course_offerings/enrollments)  
- [x] إضافة Test يمنع تعديل/إنشاء شعبة لسنة مغلقة  
- [x] إضافة Test للقيود Unique للصف/الشعبة
- [x] إضافة Test يثبت تحديث `sections_flat_list_year_{yearId}` بعد إنشاء/حذف شعبة

---

## مخرجات متوقعة

- لا يمكن حذف صف/شعبة لها بيانات مرتبطة (بدون Loss/Cascade).  
- تعديل/إنشاء الشعب ممنوع في السنة المغلقة.  
- رسائل واضحة عند التعارض مع قيود الـ Unique.  
- الكاش موحّد ولا تظهر بيانات قديمة.

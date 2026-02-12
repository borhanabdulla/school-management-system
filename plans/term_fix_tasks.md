# خطة إصلاح دومين الترم الدراسي - تاسكات تنفيذ

**المرجع:** هذه الخطة تدمج الفحص التفصيلي مع الأولويات العملية.  
**مبدأ التنفيذ:** لا تغيير لسلوك يعمل إلا إذا كان يسبب Crash، خلل منطقي مؤكد، أو ضوضاء آمنة للحذف.

---

## نطاق الإصلاح (وفق فحص MCP)

- **Routes ذات الصلة:**  
  - `academic-years/terms/{year_id?}` (terms.index)
- **جدول قاعدة البيانات:** `terms`  
  أعمدة مهمة: `academic_year_id`, `start_date`, `end_date`, `status`, `order_index`
- **قيد قاعدة البيانات:**  
  - `terms_one_active_per_year` (Term نشط واحد لكل سنة)

---

## TR-0 — نقطة الدخول (Crash Safety)

**الهدف:** التأكد أن صفحة الترمات تعمل فعليًا بدون Crash.

- [x] **terms.index** موجود ويقوم بتركيب Livewire.  
  - الملف: `resources/views/terms/index.blade.php`

---

## TR-1 — اتساق حالة الترم في الواجهة

**الهدف:** منع تضليل الحالة (Completed بالزمن بدل الحالة الفعلية).

- [x] تحديث منطق عرض الحالة ليعتمد على `TermStatus` فقط.  
  - الملف: `resources/views/components/academic/term-card.blade.php`

---

## TR-2 — حراس الكتابة (Hard Rule)

**الهدف:** منع إنشاء/تعديل الترم في سنة مغلقة + منع حالات مكتملة غير صريحة.

- [x] منع إنشاء ترم لسنة مغلقة.  
  - الملف: `app/Domains/Academic/Term/Services/TermService.php`
- [x] منع تعديل ترم في سنة مغلقة.  
  - الملف: `app/Domains/Academic/Term/Services/TermService.php`
- [x] منع إنشاء/تعديل ترم بحالة `completed` يدويًا (توضيح بدل الفشل الضمني).  
  - الملف: `app/Domains/Academic/Term/Services/TermService.php`

---

## TR-2.5 — حماية الحذف (Critical)

**الهدف:** منع حذف ترم لديه بيانات مرتبطة (خطر فقد بيانات أو Cascade).

- [x] تعريف علاقات الترم الأساسية (timetables/attendances/gradebookMonths/termResults/... إلخ).  
  - الملف: `app/Domains/Academic/Term/Models/Term.php`
- [x] إضافة هذه العلاقات إلى `protectedRelations`.  
  - الملف: `app/Domains/Academic/Term/Models/Term.php`
- [x] توحيد رسالة الحذف عبر `getDeletionBlockers()` بدل فحوصات `method_exists`.  
  - الملف: `app/Domains/Academic/Term/Services/TermService.php`

---

## TR-Observer-1 — منع الفشل الضمني (Completed Term)

**الهدف:** لا نعتمد على الـ Observer لإسقاط التعديل بصمت.

- [x] منع تعديل تواريخ ترم مكتمل برسالة واضحة قبل الوصول للـ Observer.  
  - الملف: `app/Domains/Academic/Term/Services/TermService.php`

---

## TR-Cache-1 — توحيد سياسة الكاش

**الهدف:** إزالة ازدواجية الكاش بين `TermLookupService` و `school()`.

- [x] قرار: اعتماد LookupService بالكامل وإضافة invalidate رسمي **أو** حذف invalidate غير المستخدم.  
  - الملف: `app/Domains/Academic/Term/Services/TermLookupService.php`
  - الملف: `app/Domains/Academic/Term/Observers/TermObserver.php`

---

## TR-DB-1 — تثبيت invariant الترم النشط

**الهدف:** توثيق/اختبار قاعدة “Term نشط واحد لكل سنة”.

- [x] إضافة Test يثبت عمل قيد `terms_one_active_per_year`.  
  - الملف: `tests/Feature/TermActivationTest.php` (أو Test جديد)

---

## TR-DB-2 (Later, Guarded) — منع NULL في التواريخ

**الهدف:** منع `Carbon::parse(null)` من المرور بصمت.

- [x] منع `start_date/end_date` الفارغة داخل TermService (تحقق صريح).  
  - الملف: `app/Domains/Academic/Term/Services/TermService.php`
- [ ] (لاحقًا) تعديل Migration لجعلها NOT NULL بعد Backfill.  
  - الملف: `database/migrations/2025_11_19_184700_create_terms_table.php` (أو Migration جديدة)

---

## TR-Policy-1 — قرار السياسة (لا تعديل بدون قرار)

**سؤال:** هل تفعيل ترم جديد يجب أن يُكمل الترم السابق تلقائيًا؟  
- الملف: `app/Domains/Academic/Term/Actions/ActivateTermAction.php`

**المعتمد:** نعم، تفعيل ترم جديد يُكمل الترم النشط الحالي تلقائيًا (status = Completed).  
- [x] تثبيت القرار كسياسة عمل واضحة (لا تغيير في السلوك).  
- [x] توضيح رسالة التأكيد في الواجهة لتطابق هذا السلوك.  
  - الملف: `resources/views/components/academic/term-card.blade.php`

---

## اختبارات مستهدفة

- [x] `tests/Feature/TermActivationTest.php`  
- [x] `tests/Feature/TermServiceTest.php`  
- [x] إضافة Test يمنع حذف ترم به تبعيات  
- [x] إضافة Test لمنع إنشاء/تعديل ترم لسنة مغلقة

# خطة إصلاح دومين السنة الأكاديمية - تاسكات تنفيذ

**المرجع:** هذا الملف يترجم الخطة المعتمدة إلى تاسكات تنفيذية دقيقة ومحددة بالأماكن.  
**مبدأ التنفيذ:** لا تغيير لسلوك يعمل إلا إذا كان يسبب Crash، خلل منطقي مؤكد، أو ضوضاء آمنة للحذف.

---

## نطاق الإصلاح (وفق فحص MCP)

- **Routes ذات الصلة:**  
  - `academic-years`  
  - `academic-years/terms/{year_id?}`  
  - `academic-years/{year}/close`
- **جدول قاعدة البيانات:** `academic_years`  
  أعمدة مهمة: `status`, `financial_status`, `weekend_days`, `financial_closed_by`, `start_date`, `end_date`

---

## PR-AY-0 — إصلاح أعطال التشغيل (Runtime Fixes)

**الهدف:** منع أي Crash مباشر بدون تغيير في منطق الأعمال.

- [x] **YearClosingWizard**: حذف البارامتر الزائد عند استدعاء الإغلاق.  
  - الملف: `app/Livewire/Academic/YearClosingWizard.php`  
  - التغيير:  
    - من: `execute($this->year, Auth::user())`  
    - إلى: `execute($this->year)`

- [x] **ControlDashboard**: استبدال استدعاء method غير موجودة.  
  - الملف: `app/Livewire/Admin/Control/ControlDashboard.php`  
  - التغيير:  
    - من: `AcademicYearService::getAcademicYearsList()`  
    - إلى: `AcademicYearLookupService::getList()` (أو equivalent متاح فعليا)

- [x] **ApplyDiscountAction**: إزالة تمرير بارامتر لا يقبله الاستثناء.  
  - الملف: `app/Domains/Finance/Actions/ApplyDiscountAction.php`  
  - التغيير:  
    - من: `new AcademicYearClosedException($name)`  
    - إلى: `new AcademicYearClosedException()`

**تحقق سريع:**  
- فتح صفحة الداشبورد.  
- فتح معالج الإغلاق وتنفيذ الإغلاق (لا crash).  
- تطبيق خصم على بند فاتورة (لا crash).

---

## PR-AY-1 — تصحيح مصدر الحقيقة للطلاب + الحذف/الإحصاءات

**الهدف:** منع حذف سنة تحتوي تسجيلات تاريخية وتثبيت الإحصاءات عبر السنين.

- [x] **AcademicYear علاقات ثابتة**: إضافة علاقات تعتمد على Enrollments.  
  - الملف: `app/Domains/Academic/AcademicYear/Models/AcademicYear.php`  
  - إضافات:  
    - `enrollments()`  
    - `enrolledStudents()` (via enrollments)

- [x] **قواعد الحذف الحساسة**:  
  - تحديث `canBeDeleted()` لاستخدام `enrollments()->exists()`  
  - تحديث `protectedRelations` لتتضمن `enrollments`

- [x] **الإحصاءات**:  
  - تحديث `scopeWithStats()` لإضافة `enrollments_count`  
  - الإبقاء على `students_count` كما هو لتفادي كسر واجهات تعتمد "الحالي"

- [x] **DeleteAcademicYearAction**:  
  - الملف: `app/Domains/Academic/AcademicYear/Actions/DeleteAcademicYearAction.php`  
  - استبدال التحقق من `students()` بـ `enrollments()` لمنع حذف بيانات تاريخية

- [x] **تعليق مضلل (اختياري)**:  
  - الملف: `app/Domains/Academic/AcademicYear/Models/AcademicYear.php`  
  - تحديث تعليق حذف الفصول/الشُعب ليعكس السلوك الفعلي

**تحقق سريع:**  
- سنة قديمة بها Enrollments → `enrollments_count` ثابت حتى بعد الترحيل.  
- محاولة حذف سنة Pending بها Enrollments → مرفوضة.

---

## PR-AY-3 — توحيد منطق الجاهزية مع الإغلاق

**الهدف:** منع التضليل (Readiness تقول جاهز بينما الإغلاق يرفض).

- [x] **نتائج سنوية Pending**  
  - الملفات:  
    - `app/Domains/Academic/AcademicYear/Validation/AcademicYearClosureValidator.php`  
    - `app/Domains/Academic/Services/ReadinessService.php`  
  - التغيير: استخدام `ResultDecision::Pending` أو Scope `AnnualResult::pending()`

- [x] **ترحيل الطلاب**  
  - الملف: `app/Domains/Academic/Services/ReadinessService.php`  
  - التغيير: استخدام نفس eligibility subquery/service المستخدم في ClosureValidator

**تحقق سريع:**  
- نفس عدد “غير المرحّلين” يظهر في Readiness و ClosureValidator.  
- الإغلاق ينجح إذا كانت كل الـ Blocking Issues = صفر.

---

## PR-AY-4 — توحيد مصدر السنة النشطة (Hardening محدود)

**الهدف:** تطبيق قاعدة `school()->activeYearId()` في نقاط حساسة فقط.

- [x] تحديث الاستعلامات المباشرة للسنة النشطة إلى `school()` في:  
  - `app/Livewire/Teacher/Grading/TeacherGradebooks.php`  
  - `app/Domains/Academic/Grading/Services/GradingSettingsService.php`

**تحقق سريع:**  
- في وجود أكثر من سنة: الصفحات المذكورة تطابق `school()->activeYearId()`.

---

## PR-AY-5 — تنظيف ضوضاء آمنة (Safe Cleanup)

**الهدف:** إزالة كود غير مستخدم بدون تأثير سلوكي.

- [x] إزالة `validateDateOverlap()` غير المستخدم أو توحيد الاستخدام عبر Service واحد.  
  - الملف: `app/Domains/Academic/AcademicYear/Validation/AcademicYearValidator.php`

- [x] إزالة `checkIfCanBeClosed()` غير المستخدم.  
  - الملف: `app/Domains/Academic/AcademicYear/Services/AcademicYearService.php`

- [x] إزالة حقن غير مستخدم في Livewire.  
  - الملف: `app/Livewire/Academic/AcademicYearManager.php`

- [x] مراجعة `$cacheKeys` في موديل السنة وتوحيدها مع LookupService أو حذفها إذا غير مستخدمة.  
  - الملف: `app/Domains/Academic/AcademicYear/Models/AcademicYear.php`

---

## اختبارات مستهدفة (بعد كل PR)

- [x] `tests/Feature/AcademicYearTest.php`  
- [x] `tests/Feature/AcademicYearLifecycleTest.php`  
- [x] `tests/Feature/AcademicYearClosureValidatorTest.php`  
- [x] إضافة Test جديد لثبات `enrollments_count` بعد الترحيل  
- [x] إضافة Test لمنع حذف سنة Pending فيها Enrollments

---

## مخرجات متوقعة

- لا توجد أعطال تشغيل عند فتح الواجهات الأساسية المرتبطة بالسنة.  
- لا يمكن حذف سنة بها بيانات تاريخية.  
- Readiness والإغلاق يعرضان نفس الواقع.  
- مصدر السنة النشطة موحّد في النقاط الحساسة.  
- تنظيف آمن للكود غير المستخدم بدون تغيير سلوك.
